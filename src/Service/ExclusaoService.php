<?php

namespace App\Service;

use App\Exception\RegistroEmUsoException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class ExclusaoService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @throws RegistroEmUsoException quando uma chave estrangeira impede a exclusão
     */
    public function excluir(object $entidade): void
    {
        try {
            $this->em->remove($entidade);
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new RegistroEmUsoException('Registro vinculado a outros dados.', previous: $e);
        }
    }
}
