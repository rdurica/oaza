<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Grid\News;

use App\Component\Component;
use App\Modules\Admin\Manager\NewsManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * News grid.
 *
 * @package   App\Modules\Admin\Component\Grid\News
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class News extends Component
{
    /**
     * Constructor.
     *
     * @param NewsManager $newsManager
     * @param Translator  $translator
     */
    public function __construct(
        public readonly NewsManager $newsManager,
        public readonly Translator $translator
    ) {
    }


    /**
     * Create News grid.
     *
     * @return DataGrid
     * @throws DataGridException
     */
    public function createComponentGrid(): DataGrid
    {
        $grid = new DataGrid();
        $grid->setDataSource($this->newsManager->getEntityTable()->order("id DESC"));
        $grid->addColumnText('text', $this->translator->trans('forms.news'))
            ->setRenderer(renderer: function ($item): Html {
                return Html::el()->setHtml($item->text);
            });
        $grid->addColumnText('display_on_homepage', $this->translator->trans('forms.newsOnHomepage'))
            ->setRenderer(fn($item): string => $this->convertToYesNo($item->display_on_homepage));
        $grid->addColumnText('is_draft', $this->translator->trans('forms.isDraft'))
            ->setRenderer(fn($item): string => $this->convertToYesNo($item->is_draft));
        $grid->addAction('edit', $this->translator->trans('button.edit'), 'News:Edit')
            ->setIcon('pencil')
            ->setClass('btn btn-info btn-xs');
        $grid->addAction('delete', $this->translator->trans('button.delete'), 'delete!')
            ->setIcon('trash')
            ->setClass('btn btn-danger btn-xs');
        return $grid;
    }

    /**
     * Convert int/bool value to string Yes / No
     *
     * @param bool|int $value
     * @return string
     */
    private function convertToYesNo(bool|int $value): string
    {
        return $value ? "Ano" : "Ne";
    }

    /**
     * Delete action.
     *
     * @param int $id
     * @return void
     * @throws AbortException
     */
    public function handleDelete(int $id): void
    {
        $this->newsManager->delete($id);
        $this->getPresenter()->flashMessage($this->translator->trans("flash.newsDeleted"), FlashType::SUCCESS);
        $this->getPresenter()->redirect("News:");
    }
}
