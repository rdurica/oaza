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

    public function deleteById(int $id): void
    {
        $this->getEntityTable()->where("id = ?", $id)->delete();
    }

    public function save(?int $id, string $title, bool $isOnHomepage, string $text): void
    {
        if ($id) {
            $this->update($id, $title, $isOnHomepage, $text);
        } else {
            $this->insert($title, $isOnHomepage, $text);
        }
    }

    private function insert(string $title, bool $isOnHomepage, string $text): void
    {
        $this->getEntityTable()->insert([
            "name" => $title,
            "text" => $text,
            "show" => 1,
            "show_homepage" => $isOnHomepage,
        ]);
    }

    private function update(int $id, string $title, bool $isOnHomepage, string $text): void
    {
        $this->getEntityTable()->where("id = ?", $id)->update([
            "name" => $title,
            "text" => $text,
            "show" => 1,
            "show_homepage" => $isOnHomepage,
        ]);
    }
}
