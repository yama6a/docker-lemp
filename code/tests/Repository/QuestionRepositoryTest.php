<?php declare(strict_types=1);

namespace Tests\Repository;

use App\Exceptions\NotFoundHttpException;
use App\Model\Option;
use App\Model\Question;
use App\Repository\OptionRepository;
use App\Repository\QuestionRepository;
use Tests\FrameworkTest;

final class QuestionRepositoryTest extends FrameworkTest
{
    private OptionRepository $oRepo;
    private QuestionRepository $qRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oRepo = new OptionRepository($this->conn);
        $this->qRepo = new QuestionRepository($this->conn, $this->oRepo);
        $this->oRepo->truncate();
        $this->qRepo->truncate();
    }

    protected function tearDown(): void
    {
        $this->oRepo->truncate();
        $this->qRepo->truncate();
        parent::tearDown();
    }

    public function testAllAndInsert()
    {
        $question1 = new Question(null, "MULTI_CHOICE", 'qtitle1', [
            new Option(null, null, "otext1.1", true),
            new Option(null, null, "otext1.2", false)
        ]);
        $this->qRepo->insert($question1);

        $question2 = new Question(null, "SINGLE_CHOICE", 'qtitle2', [
            new Option(null, null, "otext2.2", true),
            new Option(null, null, "otext2.2", false),
            new Option(null, null, "otext2.3", false)
        ]);
        $this->qRepo->insert($question2);

        $questions = $this->qRepo->all();
        usort($questions, fn(Question $a, Question $b) => $a->id <=> $b->id);

        $this->assertCount(2, $questions);
        $this->assertCount(2, $questions[0]->options);
        $this->assertCount(3, $questions[1]->options);

        $this->assertNotNull($questions[0]->id);
        $this->assertEquals("MULTI_CHOICE", $questions[0]->type);
        $this->assertEquals("qtitle1", $questions[0]->title);
        $this->assertNotNull($questions[0]->options[0]->id);
        $this->assertNotNull($questions[0]->options[0]->questionId);
        $this->assertEquals("otext1.1", $questions[0]->options[0]->text);
        $this->assertTrue($questions[0]->options[0]->isCorrect);
        $this->assertNotNull($questions[0]->options[1]->id);
        $this->assertNotNull($questions[0]->options[1]->questionId);
        $this->assertEquals("otext1.2", $questions[0]->options[1]->text);
        $this->assertFalse($questions[0]->options[1]->isCorrect);
    }


    public function testFind()
    {
        $question = new Question(null, "MULTI_CHOICE", 'qtitle1', [
            new Option(null, null, "otext1.1", true),
            new Option(null, null, "otext1.2", false)
        ]);
        $insertedQuestion = $this->qRepo->insert($question);
        $foundQuestion = $this->qRepo->find($insertedQuestion->id);

        $this->assertNotNull($insertedQuestion->id);
        $this->assertNotNull($foundQuestion->id);
        $this->assertEquals($insertedQuestion->id, $foundQuestion->id);
        $this->assertEquals("MULTI_CHOICE", $foundQuestion->type);
        $this->assertEquals("qtitle1", $foundQuestion->title);

        $this->assertNotNull($foundQuestion->options[0]->id);
        $this->assertEquals($insertedQuestion->id, $foundQuestion->options[0]->questionId);
        $this->assertEquals("otext1.1", $foundQuestion->options[0]->text);
        $this->assertTrue($foundQuestion->options[0]->isCorrect);

        $this->assertNotNull($foundQuestion->options[1]->id);
        $this->assertEquals($insertedQuestion->id, $foundQuestion->options[1]->questionId);
        $this->assertEquals("otext1.2", $foundQuestion->options[1]->text);
        $this->assertFalse($foundQuestion->options[1]->isCorrect);
    }

    public function testDelete()
    {
        $question = new Question(null, "MULTI_CHOICE", 'qtitle1', [
            new Option(null, null, "otext1.1", true),
            new Option(null, null, "otext1.2", false)
        ]);
        $insertedQuestion = $this->qRepo->insert($question);
        $foundQuestion = $this->qRepo->find($insertedQuestion->id);
        $this->assertNotNull($foundQuestion);


        $this->qRepo->delete($insertedQuestion->id);

        try {
            $this->qRepo->find($insertedQuestion->id);
            $this->fail("Expected exception NotFoundException");
        } catch (NotFoundHttpException $e) {
            $this->assertStringContainsString("not found", $e->getMessage());
        }
    }


    public function testUpdate()
    {
        $question = new Question(null, "MULTI_CHOICE", 'qtitle1', [
            new Option(null, null, "otext1.1", true),
            new Option(null, null, "otext1.2", false)
        ]);
        $insertedQuestion = $this->qRepo->insert($question);
        $foundQuestion = $this->qRepo->find($insertedQuestion->id);

        $this->assertNotNull($foundQuestion);
        $this->assertEquals("MULTI_CHOICE", $foundQuestion->type);
        $this->assertEquals("qtitle1", $foundQuestion->title);
        $this->assertCount(2, $foundQuestion->options);
        $this->assertEquals("otext1.1", $foundQuestion->options[0]->text);
        $this->assertTrue($foundQuestion->options[0]->isCorrect);
        $this->assertEquals("otext1.2", $foundQuestion->options[1]->text);
        $this->assertFalse($foundQuestion->options[1]->isCorrect);

        $foundQuestion->type = "SINGLE_CHOICE";
        $foundQuestion->title = "qtitle2";
        $foundQuestion->options = [
            new Option(null, null, "otext2.1", true),
            new Option(null, null, "otext2.2", false),
            new Option(null, null, "otext2.3", false)
        ];
        $this->qRepo->update($foundQuestion);

        $foundQuestion = $this->qRepo->find($insertedQuestion->id);
        $this->assertNotNull($foundQuestion);
        $this->assertEquals("SINGLE_CHOICE", $foundQuestion->type);
        $this->assertEquals("qtitle2", $foundQuestion->title);
        $this->assertCount(3, $foundQuestion->options);
        $this->assertEquals("otext2.1", $foundQuestion->options[0]->text);
    }
}
