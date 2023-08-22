<?php

namespace Tests\Feature;

use App\Http\Resources\Lesson\LessonResource;
use App\Models\Lesson;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class LessonTest extends TestCase
{
    public function test_not_auth(): void
    {
        $response = $this->json('get', route('lesson.index'));

        $response->assertStatus(401);
    }

    public function test_view(): void
    {
        $lesson = Lesson::factory()->create();

        $user = User::factory()->hasAttached($lesson)->create([
            'subscription_expires_at' => Carbon::now()->addMonths(5)
        ]);

        $response = $this->json(
            'get',
            route('lesson.index'),
            [
                'id' => $lesson->id
            ],
            $this->getHeadersForUser($user)
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'status',
            'data'
        ]);

        $this->assertSameResource(new LessonResource($lesson), $response->json('data'));
    }

    public function test_index(): void
    {
        $response = $this->json(
            'get',
            route('lesson.index'),
            headers: $this->getHeadersForUser()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure($this->getPaginationResponse());
    }
}