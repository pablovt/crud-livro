<?php

namespace App\Form;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class, [
                'label' => 'Título',
                'empty_data' => '',
                'attr' => ['maxlength' => 40],
            ])
            ->add('editora', TextType::class, [
                'label' => 'Editora',
                'empty_data' => '',
                'attr' => ['maxlength' => 40],
            ])
            ->add('edicao', IntegerType::class, [
                'label' => 'Edição',
                'invalid_message' => 'Informe uma edição válida.',
                'attr' => ['min' => 1],
            ])
            ->add('anoPublicacao', TextType::class, [
                'label' => 'Ano de publicação',
                'empty_data' => '',
                'attr' => ['maxlength' => 4, 'inputmode' => 'numeric', 'placeholder' => 'AAAA'],
            ])
            ->add('valor', MoneyType::class, [
                'label' => 'Valor',
                'currency' => 'BRL',
                'input' => 'string',
                'grouping' => true,
                'invalid_message' => 'Informe um valor válido.',
                'attr' => ['data-mascara' => 'moeda', 'placeholder' => '0,00'],
            ])
            ->add('autores', EntityType::class, [
                'label' => 'Autores',
                'class' => Autor::class,
                'choice_label' => 'nome',
                'query_builder' => static fn (EntityRepository $r): QueryBuilder => $r->createQueryBuilder('a')->orderBy('a.nome', 'ASC'),
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
            ])
            ->add('assuntos', EntityType::class, [
                'label' => 'Assuntos',
                'class' => Assunto::class,
                'choice_label' => 'descricao',
                'query_builder' => static fn (EntityRepository $r): QueryBuilder => $r->createQueryBuilder('s')->orderBy('s.descricao', 'ASC'),
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Livro::class]);
    }
}
