<?php

namespace App\Repository;

use App\Entity\Livro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LivroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livro::class);
    }

    /**
     * Carrega autores e assuntos na mesma consulta.
     *
     * @return list<Livro>
     */
    public function listarComRelacionamentos(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.autores', 'a')->addSelect('a')
            ->leftJoin('l.assuntos', 's')->addSelect('s')
            ->orderBy('l.titulo', 'ASC')
            ->addOrderBy('a.nome', 'ASC')
            ->addOrderBy('s.descricao', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
