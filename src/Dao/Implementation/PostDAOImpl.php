<?php

namespace Powon\Dao\Implementation;

use Powon\Dao\PostDAO as PostDAO;
use Powon\Entity\Post as Post;

//parent post not implemented - going to change schema?

class PostDAOImpl implements PostDAO {

    private $db;

    /**
     * PostDaoImpl constructor.
     * @param \PDO $pdo
     */
    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * @param int $id
     * @return Post|null
     */
    public function getPostById($id)
    {
        $sql = 'SELECT p.post_id,
                p.post_date_created,
                p.post_type,
                p.path_to_resource,
                p.post_body,
                p.comment_permission,
                p.page_id,
                p.author_id,
                p.parent_post
                FROM post p
                WHERE post_id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        if ($stmt->execute()) {
            $row = $stmt->fetch();
            return ($row ? new Post($row) : null);
        } else {
            return null;
        }
    }

    /**
     * @param int $page_id
     * @return [Post] of post entities.
     */
    public function getPostsByPage($page_id){

          $sql = 'SELECT p.post_id,
            p.post_date_created,
            p.post_type,
            p.path_to_resource,
            p.post_body,
            p.comment_permission,
            p.page_id,
            p.author_id,
            p.parent_post
            FROM post p
            WHERE page_id = :page_id
            AND parent_post IS NULL
            ORDER BY p.post_date_created DESC';

          $stmt = $this->db->prepare($sql);
          $stmt->bindParam(':page_id', $page_id, \PDO::PARAM_INT);
          $stmt->execute();
          $results = $stmt->fetchAll();
          if(empty($results)){
            return [];
          } else {
              return array_map(function ($row) {
                return new Post($row);
            },$results);
          }
    }
    /**
     * @param int $author_id
     * @return [Post] of post entities
     */
    public function getPostsByAuthor($author_id){
      $sql = 'SELECT p.post_id,
              p.post_date_created,
              p.post_type,
              p.path_to_resource,
              p.post_body,
              p.comment_permission,
              p.page_id,
              p.author_id,
              p.parent_post
              FROM post p
              WHERE author_id = :author_id
              AND parent_post IS NULL
              ORDER BY p.post_date_created DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':author_id', $author_id, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        if(empty($results)){
          return [];
        }
        else{
        return array_map(function ($row) {
            return new Post($row);
        },$results);
      }
    }
    /**
     * @param Post $post
     * @return int
     */
    public function createNewPost($post)
    {
        $sql = 'INSERT INTO post(post_type, path_to_resource,
                post_body, comment_permission, page_id, author_id, parent_post)
                VALUES
                (:post_type, :path_to_resource,
                :post_body, :comment_permission, :page_id, :author_id, :parent_post)';
        $stmt = $this->db->prepare($sql);
        $stmt-> bindValue(':post_type', $post->getPostType(), \PDO::PARAM_STR);
        $stmt-> bindValue(':path_to_resource', $post->getPathToResource(), \PDO::PARAM_STR);
        $stmt-> bindValue(':post_body', $post->getPostBody(), \PDO::PARAM_STR);
        $stmt-> bindValue(':comment_permission', $post->getCommentPermission(), \PDO::PARAM_STR);
        $stmt-> bindValue(':page_id', $post->getPageId(), \PDO::PARAM_INT);
        $stmt-> bindValue(':author_id', $post->getAuthorId(), \PDO::PARAM_INT);
        $stmt-> bindValue(':parent_post', $post->getParentPostId(), \PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return -1;
    }

    /**
     * @param $post_id int|string
     * @return array ['member_id' => int, 'permission' => string]
     */
    public function getCustomAccessListOnPost($post_id)
    {
        $sql = 'SELECT member_id, comment_permission from custom_post_access WHERE 
            post_id = :post_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':post_id', $post_id);
        if ($stmt->execute()) {
            $res = $stmt->fetchAll();
            return array_map(function ($it) {
                return ['member_id' => (int)$it['member_id'], 'permission' => $it['comment_permission']];
            }, $res);
        }
        return [];
    }

    /**
     * Updates post body, resource_path, comment_permission, post_type
     * @param $post Post
     * @return bool
     */
    public function updatePost($post)
    {
        $sql = 'UPDATE post SET post_body = :body, path_to_resource = :path,
                comment_permission = :permission, post_type = :post_type
                WHERE post_id = :post_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':body', $post->getPostBody());
        $stmt->bindValue(':path', $post->getPathToResource());
        $stmt->bindValue(':permission', $post->getCommentPermission());
        $stmt->bindValue(':post_type', $post->getPostType());
        $stmt->bindValue(':post_id', $post->getPostId());
        return $stmt->execute();
    }

    /**
     * @param $post_id int|string the post id.
     * @return bool
     */
    public function deleteCustomAccessesForPost($post_id)
    {
        $sql = 'DELETE FROM custom_post_access WHERE post_id = :post_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':post_id', $post_id);
        return $stmt->execute();
    }

    /**
     * @param $member_id int|string the member id
     * @param $post_id int|string the post id
     * @param $permission string The permission character.
     * @return bool
     */
    public function addCustomAccessForPost($member_id, $post_id, $permission)
    {
        $sql = 'INSERT INTO custom_post_access (post_id, member_id, comment_permission) VALUES 
                (:post_id, :member_id, :permission)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':post_id', $post_id);
        $stmt->bindValue(':member_id', $member_id);
        $stmt->bindValue(':permission', $permission);
        return $stmt->execute();

    }

    public function getPermissionForMemberOnPost($post_id, $member_id)
    {
        $sql = "SELECT comment_permission FROM (
                  (SELECT p.comment_permission FROM post p WHERE p.post_id = :post_id AND p.comment_permission <> 'T')
                   UNION (SELECT c.comment_permission FROM custom_post_access c WHERE c.post_id = :post_id AND c.member_id = :member_id)
                ) as joint";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':post_id', $post_id);
        $stmt->bindValue(':member_id', $member_id);
        if ($stmt->execute()) {
            $res = $stmt->fetch();
            if ($res)
                return $res['comment_permission'];
        }
        return Post::PERMISSION_DENIED;
    }

    /**
     * @param $parent int|string The parent post id
     * @return [Post]
     */
    public function getChildrenPosts($parent)
    {
        $sql = 'SELECT p.post_id, p.post_date_created,
              p.post_type,
              p.path_to_resource,
              p.post_body,
              p.comment_permission,
              p.page_id,
              p.author_id,
              p.parent_post
              FROM post p
              WHERE p.parent_post = :parent
              ORDER BY p.post_date_created ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':parent', $parent, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        if(empty($results)){
            return [];
        } else {
            return array_map(function ($row) {
                return new Post($row);
            }, $results);
        }
    }

    /**
     * @param $post_id string|int
     * @return bool
     */
    public function deletePost($post_id)
    {
        $sql = 'DELETE FROM post WHERE post_id = :post_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':post_id', $post_id);
        return $stmt->execute();
    }
}
