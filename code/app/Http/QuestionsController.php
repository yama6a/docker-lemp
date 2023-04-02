<?php declare(strict_types=1);

namespace App\Http;

use App\Http\Exceptions\BadRequestHttpException;
use App\Model\Option;
use App\Model\Question;
use App\Repository\OptionRepository;
use Doctrine\DBAL\DBALException;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class QuestionsController implements Controller
{
    private QuestionRepository $questionRepo;
    private OptionRepository $optionRepository;

    public function __construct(QuestionRepository $questionRepository, OptionRepository $optionRepository)
    {
        $this->questionRepo = $questionRepository;
        $this->optionRepository = $optionRepository;
    }

    public function all(Request $request): JsonResponse
    {
        try {
            return ResponseFactory::make($this->questionRepo->all());
        } catch (DBALException $e) {
            return ResponseFactory::make($e);
        }
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $question = $this->parseQuestionFromRequest($request);
            $question = $this->questionRepo->insert($question);
            return ResponseFactory::make($question, 201);
        } catch (Throwable $e) {
            return ResponseFactory::make($e);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $question = $this->parseQuestionFromRequest($request);
            $this->questionRepo->update($question);
            return ResponseFactory::make("question updated");
        } catch (Throwable $e) {
            return ResponseFactory::make($e);
        }
    }

    public function deleteQuestion(Request $request): JsonResponse
    {
        try {
            $json = $request->toArray();
            if (!$questionId = $json['question_id'] ?? null) {
                throw new BadRequestHttpException("question_id is missing from request.");
            }
            $this->questionRepo->delete($questionId);
            return ResponseFactory::make("question deleted");
        } catch (Throwable $e) {
            return ResponseFactory::make($e);
        }
    }

    private function parseQuestionFromRequest(Request $request): Question
    {
        $json = $request->toArray();
        if (!isset($json['question'])) {
            throw new BadRequestHttpException("question object not found in request payload.");
        }

        $q = $json['question'];
        return new Question($q['id'] ?? null, $q['type'], $q['title'], $this->parseOptionsFromRequestObj($q['options']));
    }

    /**
     * @param array $obj
     * @return array|Option[]
     */
    private function parseOptionsFromRequestObj(array $obj): array
    {
        return array_map(fn(array $o) => new Option(
            $o['id'] ?? null,
            $o['questionId'] ?? null,
            $o['text'],
            (bool)$o['isCorrect']
        ), $obj);
    }
}
