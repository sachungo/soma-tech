<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VideosTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test the user can add videos.
     *
     * @return void
     */
    public function testVideosUploaded()
    {
        $user = factory(Soma\User::class)->create();
        $category = factory(Soma\Categories::class)->create();

        $this->actingAs($user)
             ->visit('video/create')
             ->type('https://www.youtube.com/embed/qVM7cwqGTTU&index=4&list=RDH1XrbuOoKFw', 'youtube_link')
             ->type('I will worship you', 'title')
             ->type('Gospel worship video', 'description')
             ->select($category->id, 'category_id')
             ->press('Add');

        $this->seeInDatabase('videos', ['title' => 'I will worship you']);
    }

    /**
     * Test the user can see their uploaded videos.
     *
     * @return void
     */
    public function testPersonallyUploadedVideosViewed()
    {
        $user = factory(Soma\User::class)->create();
        $category = factory(Soma\Categories::class)->create();

        factory(Soma\Videos::class, 3)->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            ]);

        factory(Soma\Videos::class)->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'youtube_link' => 'https://www.youtube.com/embed/6R0YdAnASc8&index=23&list=RDH1XrbuOoKFw',
            'title' => 'MERCY SAID NO!',
            'description' => 'Judy Jacobs sings MERCY SAID NO!',
            ]);

        $this->actingAs($user)
             ->visit('video/myvideos')
             ->see('MERCY SAID NO!');
    }

    /**
     * Test the videos can be edited.
     *
     * @return void
     */
    public function testVideosEdited()
    {
        $user = factory(Soma\User::class)->create();
        $category = factory(Soma\Categories::class)->create();

        $video = factory(Soma\Videos::class)->create([
                 'category_id' => $category->id,
                 'user_id' => $user->id,
                 'youtube_link' => 'https://www.youtube.com/embed/6R0YdAnASc8&index=23&list=RDH1XrbuOoKFw',
                 'title' => 'MERCY SAID NO!',
                 'description' => 'Judy Jacobs sings MERCY SAID NO!',
            ]);

        $this->actingAs($user)
             ->visit('video/myvideos')
             ->click('edit-video')
             ->seePageIs("video/{$video->id}/edit")
             ->see('MERCY SAID NO!')
             ->type('Gospel - Mercy Said No!', 'title')
             ->type('Mercy Said No song as sang by Judy Jacobs', 'description')
             ->press('Save')
             ->seePageIs('video/myvideos');

        $this->seeInDatabase(
            'videos',
            [
              'title' => 'Gospel - Mercy Said No!',
              'description' => 'Mercy Said No song as sang by Judy Jacobs',
            ]
        );
    }

    /**
     * Test the videos can be deleted.
     *
     * @return void
     */
    public function testVideosDeleted()
    {
        $user = factory(Soma\User::class)->create();
        $category = factory(Soma\Categories::class)->create();

        factory(Soma\Videos::class)->create([
                 'category_id' => $category->id,
                 'user_id' => $user->id,
                 'youtube_link' => 'https://www.youtube.com/embed/z3wwWFsSlNQ&list=RDH1XrbuOoKFw&index=27',
                 'title' => 'Still - Hillsong United with Lyrics!',
                 'description' => "Appreciating God's love for us as Jesus died on the cross to save our souls",
            ]);

        $this->actingAs($user)
             ->visit('video/myvideos')
             ->press('delete-video');

        $this->notSeeInDatabase(
            'videos',
            [
              'title' => 'Still - Hillsong United with Lyrics!',
              'description' => "Appreciating God's love for us as Jesus died on the cross to save our souls",
            ]
        );
    }

    /**
     * Test a video can be viewed on its own page.
     *
     * @return void
     */
    public function testSingleVideoViewed()
    {
        $user = factory(Soma\User::class)->create();
        $category = factory(Soma\Categories::class)->create();

        $video = factory(Soma\Videos::class)->create([
                 'category_id' => $category->id,
                 'user_id' => $user->id,
                 'youtube_link' => 'https://www.youtube.com/embed/gIPMllUV12o&list=RDH1XrbuOoKFw&index=27',
                 'title' => 'My life is in your hands',
                 'description' => "Kirk Franklin's Gospel song,"
            ]);

        $this->actingAs($user)
             ->visit('video/myvideos')
             ->see('My life is in your hands')
             ->click('More Info')
             ->seePageIs("video/{$video->id}");
    }
}