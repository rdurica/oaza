<?php

declare(strict_types=1);

namespace App\Modules\Admin\Manager;

use App\Model\Manager;
use Nette\Database\Table\ActiveRow;
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
     * @return ActiveRow|null
     */
    public function findHomepageNews(): ?ActiveRow
    {
        return $this->getEntityTable()
            ->where('display_on_homepage = ?', 1)
            ->where("is_draft = ?", 0)
            ->order('id DESC')
            ->fetch();
    }

    /**
     * Create or update news.
     *
     * @param int|null $id
     * @param string   $title
     * @param bool     $isOnHomepage
     * @param string   $text
     * @param bool     $isDraft
     * @return void
     */
    public function save(string $text, bool $isOnHomepage = true, bool $isDraft = false, int $id = null): ?ActiveRow
    {
        if ($id) {
            $this->update($id, $text, $isOnHomepage, $isDraft);
        } else {
            return $this->insert($text, $isOnHomepage, $isDraft);
        }
    }

    /**
     * Create new news.
     *
     * @param string $text
     * @param bool   $isOnHomepage
     * @param bool   $isDraft
     * @return void
     * @see NewsManager::save()
     */
    private function insert(string $text, bool $isOnHomepage, bool $isDraft): ?ActiveRow
    {
        return $this->getEntityTable()->insert([
            "text" => $text,
            "is_draft" => $isDraft,
            "display_on_homepage" => $isOnHomepage,
        ]);
    }

    /**
     * Update news by id.
     *
     * @param int    $id
     * @param string $text
     * @param bool   $isOnHomepage
     * @param bool   $isDraft
     * @return void
     * @see NewsManager::save()
     */
    private function update(int $id, string $text, bool $isOnHomepage, bool $isDraft): void
    {
        $this->getEntityTable()->where("id = ?", $id)->update([
            "text" => $text,
            "is_draft" => $isDraft,
            "display_on_homepage" => $isOnHomepage,
        ]);
    }
}
