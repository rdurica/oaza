<?php declare(strict_types=1);

namespace App\Component\Form\UserEdit;

/**
 * Factory for user edit form.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
interface UserEditFormFactory
{
    /**
     * Create form.
     *
     * @param int|null $id
     * @return UserEdit
     */
    public function create(?int $id): UserEdit;
}
