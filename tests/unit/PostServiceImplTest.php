<?php

use PHPUnit\Framework\TestCase;
use Powon\Services\PostService;
use Powon\Test\Stub\LoggerStub;
use Powon\Test\Stub\PostDaoStub;


class PostServiceImplTest extends TestCase
{

    /**
     * @var PostService $postService
     */
    private $postService;

    public function setUp()
    {
      parent::setUp();
    $dao = new PostDaoStub();
    $dao->posts = array(
      [
        'post_id' => 1,
        'post_date_created' => '2016-06-01',
        'post_type' => 't',
        'path_to_resource' => 'members/admin',
        'post_body' => 'my favourite fish is a happy fish',
        'comment_permission' => 'p',
        'page_id' => 1,
        'author_id' => 1
        ],
        [
          'post_id' => 2,
          'post_date_created' => '2016-06-03',
          'post_type' => 't',
          'path_to_resource' => 'members/admin',
          'post_body' => 'omfg ME TOO love those lil guys',
          'comment_permission' => 'p',
          'page_id' => 1,
          'author_id' => 2
        ],
        [
            'post_id' => 3,
            'post_date_created' => '2016-06-04',
            'post_type' => 't',
            'path_to_resource' => 'members/admin',
            'post_body' => 'lets be pals',
            'comment_permission' => 'p',
            'page_id' => 1,
            'author_id' => 1
        ],);
    $logger = new LoggerStub();
    $this->postService = new \Powon\Services\Implementation\PostServiceImpl($logger,$dao);
}

public function testGetPostById(){
  $res = $this->postService->getPostById(2);
  $this->assertEquals($res->getPostBody(), 'omfg ME TOO love those lil guys');
}

public function testGetPostsByPage(){
  $res = $this->postService->getPostsByPage(1);
  $this->assertEquals(count($res), 3);
}

public function testGetPostsByAuthor(){
  $res = $this->postService->getPostsByAuthor(1);
  $this->assertEquals(count($res), 2);
}

public function testCreateNewPost(){
  $res = $this->postService->createNewPost('t', '/members/admin', 'im a new post!', 'p', '2', '1');
  $this->assertTrue($res['success']);
}

}