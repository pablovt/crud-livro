<?php

namespace App\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SchemaTest extends KernelTestCase
{
    public function testMapeamentoDasEntidadesEValido(): void
    {
        $validator = new SchemaValidator(self::getContainer()->get(EntityManagerInterface::class));

        self::assertSame([], $validator->validateMapping());
    }

    public function testMapeamentoEstaEmSincroniaComOsScriptsSql(): void
    {
        $validator = new SchemaValidator(self::getContainer()->get(EntityManagerInterface::class));

        $diferencas = array_filter(
            $validator->getUpdateSchemaList(),
            static fn (string $sql): bool => 1 !== preg_match('/RENAME INDEX livro_(autor|assunto)_fkindex[12] /i', $sql),
        );

        self::assertSame([], array_values($diferencas));
    }
}
