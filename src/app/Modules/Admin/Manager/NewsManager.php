<?php

declare(strict_types=1);

namespace App\Modules\Admin\Manager;

use App\Model\Manager;
use Nette\Database\Table\Selection;

/**
 * NewsManager.
 *
 * @package   App\Modules\Admin\Manager
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
final class NewsManager extends Manager
{
    /** @inheritDoc */
    public function getEntityTable(): Selection
    {
        return $this->database->table("news");
    }


    /**
     * Find last news which will be displayed on homepage.
     *
     * @return Selection
     */
    public function findHomepageNews(): Selection
    {
        return $this->getEntityTable()
            ->where('show_homepage = 1')
            ->order('id DESC')
            ->limit(1);
    }


    /**
     * Delete news by id.
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        $this->getEntityTable()->where("id = ?", $id)->delete();
    }

    /**
     * Create or update news.
     *
     * @param int|null $id
     * @param string   $title
     * @param bool     $isOnHomepage
     * @param string   $text
     * @param bool     $show
     * @return void
     */
    public function save(?int $id, string $title, bool $isOnHomepage, string $text, bool $show = true): void
    {
        if ($id) {
            $this->update($id, $title, $isOnHomepage, $text, $show);
        } else {
            $this->insert($title, $isOnHomepage, $text, $show);
        }
    }

    /**
     * Create new news.
     *
     * @param string $title
     * @param bool   $isOnHomepage
     * @param string $text
     * @param bool   $show
     * @return void
     * @see NewsManager::save()
     */
    private function insert(string $title, bool $isOnHomepage, string $text, bool $show = true): void
    {
        $this->getEntityTable()->insert([
            "name" => $title,
            "text" => $text,
            "show" => 1,
            "show_homepage" => $isOnHomepage,
        ]);
    }

    /**
     * Update news by id.
     *
     * @param int    $id
     * @param string $title
     * @param bool   $isOnHomepage
     * @param string $text
     * @param bool   $show
     * @return void
     * @see NewsManager::save()
     */
    private function update(int $id, string $title, bool $isOnHomepage, string $text, bool $show = true): void
    {
        $this->getEntityTable()->where("id = ?", $id)->update([
            "name" => $title,
            "text" => $text,
            "show" => $show,
            "show_homepage" => $isOnHomepage,
        ]);
    }
}
