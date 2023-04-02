<?php

namespace Tests\Repository;

// tests for options repository
use App\Exceptions\NotFoundHttpException;
use App\Model\Option;
use App\Model\Question;
use App\Repository\OptionRepository;
use App\Repository\QuestionRepository;
use Tests\FrameworkTest;

class OptionRepositoryTest extends FrameworkTest
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

    public function testInsertAndFind()
    {
        $q = $this->insertQuestion();

        $option = new Option(null, $q->id, "otext1.1", true);
        $option = $this->oRepo->insert($option);
        $option = $this->oRepo->find($option->id);

        $this->assertNotNull($option->id);
        $this->assertNotNull($option->questionId);
        $this->assertEquals("otext1.1", $option->text);
        $this->assertTrue($option->isCorrect);
    }

    // test deleteByQuestionId
    public function testDeleteByQuestionId()
    {
        $q = $this->insertQuestion();

        $option = new Option(null, $q->id, "otext1.1", true);
        $option = $this->oRepo->insert($option);
        $option = $this->oRepo->find($option->id);

        $this->oRepo->deleteByQuestionId($q->id);
        try {
            $this->oRepo->find($option->id);
            $this->fail("Option should not be found");
        } catch (NotFoundHttpException $e) {
            $this->assertStringContainsString("not found", $e->getMessage());
        }
    }

    // test getByQuestionId
    public function testGetByQuestionId()
    {
        $q = $this->insertQuestion();

        $option1 = new Option(null, $q->id, "otext1.1", true);
        $option1 = $this->oRepo->insert($option1);
        $option1 = $this->oRepo->find($option1->id);

        $option2 = new Option(null, $q->id, "otext1.2", false);
        $option2 = $this->oRepo->insert($option2);
        $option2 = $this->oRepo->find($option2->id);

        $options = $this->oRepo->getByQuestionId($q->id);
        $optionIds = array_map(fn($o)=>$o->id, $options);

        $this->assertCount(2, $options);
        $this->assertContains($option1->id, $optionIds);
        $this->assertContains($option2->id, $optionIds);
    }

    private function insertQuestion()
    {
        return $this->qRepo->insert(new Question(null, "MULTI_CHOICE", 'qtitle1', []));
    }
}
