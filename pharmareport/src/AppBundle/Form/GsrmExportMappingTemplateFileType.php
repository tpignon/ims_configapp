<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\GsrmExportMappingTemplateFile;

class GsrmExportMappingTemplateFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('datasetName', ChoiceType::class, array(
              'expanded' => false,
              'multiple' => false,
              'label' => 'Select a dataset :',
              'choices' => array(
                  'Select a dataset ...' => false,
                  'Boiron - PTR (ClientOutputID : 3970)' => '3970',
                  'MSD - CCC - Gastro (ClientOutputID : 3391)' => '3391',
                  'MSD - CCC - Rheumato (ClientOutputID : 3392)' => '3392',
                  'MSD - BHA - Gastro (ClientOutputID : 91)' => '91',
                  'MSD - BHA - Rheumato (ClientOutputID : 92)' => '92',
                  'MSD - XPO - Gastro (ClientOutputID : 93)' => '93',
                  'MSD - XPO - Rheumato (ClientOutputID : 94)' => '94',
                  'Omega Pharma - PTR - Vitamine (ClientOutputID : 3853)' => '3853',
                  'Omega Pharma - PTR - Starbrands (ClientOutputID : 4049)' => '4049',
                  'Pfizer S.A. - CCC (ClientOutputID : 2597)' => '2597',
                  'Pfizer S.A. - STL (ClientOutputID : 3541)' => '3541',
                  'Pfizer S.A. - XPO - Rheumato (ClientOutputID : 97)' => '97',
                  'Pfizer S.A. - XPO - Dermato (ClientOutputID : 98)' => '98',
                  'Therabel - BCD (ClientOutputID : 3436)' => '3436',
                  'Therabel - PTR (ClientOutputID : 3980)' => '3980',
                  'Therabel - XPlain (ClientOutputID : 3438)' => '3438',
                  'Will Pharma - PTR (ClientOutputID : 4249)' => '4249',
              )
          ))
          ->add('download',   SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
          'data_class' => 'AppBundle\Entity\GsrmExportMappingTemplateFile'
        ));
    }
}
