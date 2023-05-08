<?php

declare(strict_types=1);

namespace App\Modules\Admin\Component\Grid\News;

use App\Component\Component;
use App\Model\Manager\NewsManager;
use App\Util\FlashType;
use Contributte\Translation\Translator;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

class News extends Component
{
    public function __construct(
        public readonly NewsManager $newsManager,
        public readonly Translator $translator
    ) {
    }


    /**
     * @throws DataGridException
     */
    public function createComponentGrid(): DataGrid
    {
        $grid = new DataGrid();
        $grid->setDataSource($this->newsManager->getEntityTable()->order("id DESC"));
        $grid->addColumnText('text', 'Text')
            ->setRenderer(renderer: function ($item): Html {
                return Html::el()->setHtml($item->text);
            });
        $grid->addColumnText('show_homepage', 'Na hlavni strance')
            ->setRenderer(fn($item): string => $this->convertToYesNo($item->show_homepage));
        $grid->addColumnText('show', 'Zobrazit')
            ->setRenderer(fn($item): string => $this->convertToYesNo($item->show));
        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setIcon('trash')
            ->setClass('btn btn-danger btn-xs');

        return $grid;
    }

    /**
     * Convert value to string Yes / No
     * @param bool $value
     * @return string
     */
    private function convertToYesNo(bool|int $value): string
    {
        return $value ? "Ano" : "Ne";
    }

    /**
     * Delete user handler
     * @throws AbortException
     */
    #[NoReturn] public function handleDelete(int $id): void
    {
        $this->newsManager->deleteNewsById($id);
        $this->getPresenter()->flashMessage($this->translator->trans("flash.newsDeleted"), FlashType::SUCCESS);
        $this->getPresenter()->redirect("News:");
    }
}
