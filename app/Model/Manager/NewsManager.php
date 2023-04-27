<?php

declare(strict_types=1);

namespace App\Model\Manager;

use App\Model\Model;
use Nette\Database\Table\Selection;

final class NewsManager extends Model
{
    public function getEntityTable(): Selection
    {
        return $this->database->table("news");
    }



    /**
     * Show last new on default page
     */
    public function findHomepageNews(): Selection
    {
        return $this->getEntityTable()
            ->where('show_homepage = 1')
            ->order('id DESC')
            ->limit(1);
    }


    /**
     * Get posts for Paginator
     */
    public function getPaginatorPosts($length, $offset): Selection
    {
        return $this->getEntityTable()
            ->where('show = 1')
            ->order('id DESC')
            ->limit($length, $offset);
    }
}
