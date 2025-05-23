<?php declare(strict_types=1);

namespace App\Facade;

use App\Dto\CanceledReservationDto;
use App\Dto\CreateRestrictionDto;
use App\Exception\CreateRestrictionException;
use App\Exception\DeleteRestrictionException;
use App\Model\Manager\NewsManager;
use App\Model\Manager\ReservationManager;
use App\Model\Manager\RestrictionManager;
use App\Model\Service\Mail\MailService;
use Exception;
use Nette\Database\Explorer;
use Tracy\Debugger;

/**
 * RestrictionFacade.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-20
 */
final class RestrictionFacade
{
    /**
     * Constructor.
     *
     * @param RestrictionManager $restrictionManager
     * @param NewsManager        $newsModel
     * @param ReservationManager $reservationManager
     * @param Explorer           $database
     * @param MailService        $mailService
     */
    public function __construct(
        private readonly RestrictionManager $restrictionManager,
        private readonly NewsManager $newsModel,
        private readonly ReservationManager $reservationManager,
        private readonly Explorer $database,
        private readonly MailService $mailService,
    )
    {
    }

    /**
     * Creates new restriction.
     *
     * @param int $restrictionId
     *
     * @return void
     * @throws DeleteRestrictionException
     */
    public function delete(int $restrictionId): void
    {
        try
        {
            $restriction = $this->restrictionManager->findById($restrictionId);
            if ($restriction === null)
            {
                return;
            }

            $this->reservationManager->cancelReservations($restriction->from, $restriction->to);

            $restriction->delete();
        }
        catch (Exception $e)
        {
            Debugger::log($e, Debugger::ERROR);

            throw new DeleteRestrictionException();
        }
    }

    /**
     * Creates new restriction.
     *
     * @param CreateRestrictionDto $createRestrictionDto
     *
     * @return void
     * @throws Exception
     */
    public function create(CreateRestrictionDto $createRestrictionDto): void
    {
        $this->database->beginTransaction();

        try
        {
            $this->createRestriction($createRestrictionDto);
            $this->createNews($createRestrictionDto);
            $canceledReservationDtos = $this->cancelReservations($createRestrictionDto);
            $this->restrictDaysForBooking($createRestrictionDto);

            $this->database->commit();
        }
        catch (Exception $e)
        {
            $this->database->rollBack();
            Debugger::log($e, Debugger::ERROR);

            throw new CreateRestrictionException();
        }

        $this->mailService->sendReservationCancellationByAdministrator($canceledReservationDtos);
    }

    /**
     * @param CreateRestrictionDto $createRestrictionDto
     *
     * @return void
     */
    private function createRestriction(CreateRestrictionDto $createRestrictionDto): void
    {
        $this->restrictionManager->create($createRestrictionDto->from, $createRestrictionDto->to, $createRestrictionDto->message);
    }

    /**
     * @param CreateRestrictionDto $createRestrictionDto
     *
     * @return void
     */
    private function createNews(CreateRestrictionDto $createRestrictionDto): void
    {
        if ($createRestrictionDto->showNewsOnHomepage === false)
        {
            return;
        }

        $this->newsModel->save(null, 'Omezení provozu', $createRestrictionDto->showNewsOnHomepage, $createRestrictionDto->message, false);
    }

    /**
     * @param CreateRestrictionDto $createRestrictionDto
     *
     * @return CanceledReservationDto[]
     */
    private function cancelReservations(CreateRestrictionDto $createRestrictionDto): array
    {
        return $this->reservationManager->cancelReservations($createRestrictionDto->from, $createRestrictionDto->to);
    }

    /**
     * @param CreateRestrictionDto $createRestrictionDto
     *
     * @return void
     */
    private function restrictDaysForBooking(CreateRestrictionDto $createRestrictionDto): void
    {
        $this->reservationManager->restrictDaysForBooking($createRestrictionDto->from, $createRestrictionDto->to);
    }
}
