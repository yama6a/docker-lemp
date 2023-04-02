<?php

namespace Tests\Controller;

use App\Http\Router;
use App\Model\Option;
use App\Model\Question;
use App\Repository\LeverageRepository;
use App\Repository\OptionRepository;
use App\Repository\QuestionRepository;
use Mockery;
use Symfony\Component\HttpFoundation\Request;
use Tests\FrameworkTest;

class QuestionControllerTest extends FrameworkTest
{
    private QuestionRepository $qRepo;
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->qRepo = Mockery::mock(QuestionRepository::class);
        $this->router = new Router($this->qRepo, Mockery::mock(OptionRepository::class), Mockery::mock(LeverageRepository::class));
    }

    public function testAll(): void
    {
        $q1 = $this->makeQuestion();
        $q2 = $this->makeQuestion();
        $this->qRepo->shouldReceive('all')->andReturn([$q1, $q2]);

        $response = $this->router->handle(Request::create('api.php?path=/questions'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([$q1, $q2]), json_encode(json_decode($response->getContent())->payload));
    }

    // test create
    public function testCreate(): void
    {
        $q = $this->makeQuestion();
        $this->qRepo->shouldReceive('insert')->once()->andReturn($q);
        $response = $this->router->handle(Request::create('api.php?path=/questions', 'POST', [], [], [], [], json_encode(["question" => $q])));
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
        $this->assertJsonStringEqualsJsonString(json_encode($q), json_encode(json_decode($response->getContent())->payload));
    }

    // test update
    public function testUpdate(): void
    {
        $q = $this->makeQuestion();
        $this->qRepo->shouldReceive('update')->once()->andReturn($q);
        $response = $this->router->handle(Request::create('api.php?path=/questions', 'PUT', [], [], [], [], json_encode(["question" => $q])));
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJsonStringEqualsJsonString('"question updated"', json_encode(json_decode($response->getContent())->payload));
    }

    // test delete
    public function testDelete(): void
    {
        $q = $this->makeQuestion();
        $this->qRepo->shouldReceive('delete')->once()->andReturn($q);
        $response = $this->router->handle(Request::create('api.php?path=/questions', 'DELETE', [], [], [], [], json_encode(["question_id" => $q->id])));
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertJsonStringEqualsJsonString('"question deleted"', json_encode(json_decode($response->getContent())->payload));
    }

    private function makeQuestion(): Question
    {
        $qId = rand(0, 999999999);
        return new Question($qId, "MULTI_CHOICE", 'qtitle1', [
            new Option(rand(0, 999999999), null, "otext1.1", true),
            new Option(rand(0, 999999999), null, "otext1.2", false)
        ]);
    }
}
