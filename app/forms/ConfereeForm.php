<?php

namespace App\Forms;

use App\Orm\Conferee;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class ConfereeForm
{
    use SmartObject;

    /** @var FormFactory */
    private $factory;


    /**
     * RegisterConfereeForm constructor.
     * @param FormFactory $factory
     */
    public function __construct(FormFactory $factory)
    {
        $this->factory = $factory;
    }


    /**
     * @param callable $onSuccess
     * @param Conferee|null $conferee
     * @return Form
     */
    public function create(callable $onSuccess, Conferee $conferee = null)
    {
        $form = $this->factory->create();
        $form->addText('firstName', 'Křestní jméno:')
            ->setOption('description', 'Jméno bude zobrazeno v na webu s Vaším avatarem')
            ->setRequired('Prosíme, vyplňte svoje křestní jméno');

        $form->addText('lastName', 'Příjmení:')
            ->setOption('description', 'Jméno bude zobrazeno v na webu s Vaším avatarem')
            ->setRequired('Prosíme, vyplňte svoje příjmení jméno');

        $form->addText('email', 'E-mail:')
            ->setOption('description', 'E-mail nikde nezobrazujeme, ale bude sloužit pro přihlášení a tak…')
            ->setRequired('Prosíme, vyplňte svůj e-mail');

        $form->addGroup('Dotační dotazník');

        $form->addText('city', 'Adresa tvého bydliště (stačí město)')
            ->setRequired('Prosíme, vyplňte svoje Město');

        $form->addText('psc', 'PSČ:')
            ->setRequired('Prosíme, vyplňte svoje PSČ');

        $form->addSelect('country', 'Stát:', ['cr'=>'ČR', 'sr'=>'SR', 'other'=>'Jiné'])
            ->setDefaultValue('cr')
            ->setRequired('Prosíme, vyplňte svůj stát');

        $form->addSelect('isStudent', 'Studuješ školu?', [1 => 'Ano', 0 => 'Ne'])
            ->setOption('description', 'Jsi stále ještě student?')
            ->setRequired('Prosíme, vyplň zda studuješ');

        $form->addSelect('ssVs', 'Do které kategorie právě patříš?', [
            'zs'=>'ZŠ',
            'odborne-uciliste'=>'Odborné učiliště',
            'ss'=>'SŠ',
            'vos'=>'VOŠ',
            'vs'=>'VŠ',
            'nestuduji'=>'NESTUDUJI',
            'other'=>'JINÉ,'
        ])
            ->setRequired('Prosíme, vyplň do které kategorie patříš');

        $form->addText('schoolName', 'Jakou školu studuješ, popřípadě ve které firmě pracuješ?')
            ->setRequired('Prosíme, vyplň jakou školu studuješ');

        $form->addText('schoolCity', 'V jakém městě studuješ nebo pracuješ?')
            ->setRequired('Prosíme, vyplň v jaké městě studuješ');

        $form->addText('schoolField', 'Jaký obor studuješ? (popřípadě pozice ve firmě)')
            ->setRequired('Prosíme, vyplň jaký obor studuješ');

        $form->addText('schoolLevel', 'V jakém ročníku aktuálně jsi?')
            ->setRequired('Prosíme, vyplň v jakém ročníku jsi');

        $form->addText('reasons', 'Co tě přimělo jít na Barcamp?')
            ->setRequired('Prosíme, vyplň co tě přimělo jít na Barcamp');

        $form->addText('profi', 'V čem chceš #býtprofi?')
            ->setRequired('Prosíme, vyplň v čem chceš být profi.');

        $form->addSelect('experience', 'Na kolikátý Pražský Barcamp jdeš?', [
            '1'=>'Toto je můj první ročník',
            '2'=>'Už mám jeden ročník za sebou',
            'all'=>'Zatím jsem byl na všech ročnících'
        ])
            ->setRequired('Prosíme, vyplň na kolikátý Pražský Barcamp jdeš');

        $form->addText('year', 'Kolik máš let?')
            ->setRequired('Prosíme, vyplň kolik máš let.');

        $form->addSelect('sex', 'Pohlaví', ['muz'=>'Muž', 'zena'=>'Žena'])
            ->setRequired('Prosíme, vyplň pohlaví');

        $form->addGroup();

        $form->addCheckbox('consens', 'Souhlasím se zpracováním osobních údajů de zákona č. 101/2000 Sb.')
            ->setRequired('Pro dokončení registrace potřebujeme Váš souhlas se zopracováním osobních údajů. '
                . 'Bez něho nemůžeme registraci dokončit.');

        $form->addSubmit('send')
            ->setOption('itemClass', 'text-center')
            ->getControlPrototype()->setName('button')->setText('Odeslat');

        $form->addProtection('Prosím, odešlete formulář ještě jednou');

        $form->onSuccess[] = function (Form $form, $values) use ($conferee, $onSuccess) {
            if ($conferee === null) {
                $conferee = new Conferee();
            }

            $this->formToConferee($form, $conferee);
            $onSuccess($conferee, $values);
        };

        if ($conferee) {
            $this->confereeToForm($conferee, $form);
        }

        return $form;
    }


    /**
     * @param Form $form
     * @param Conferee $conferee
     * @throws JsonException
     */
    public function formToConferee(Form $form, Conferee $conferee): void
    {
        /** @var array $values */
        $values = $form->getValues(true);

        $conferee->name = $values['firstName'] . ' ' . $values['lastName'];
        $conferee->email = $values['email'];
        $conferee->extended = Json::encode([
            'firstName' => $values['firstName'],
            'lastName' => $values['lastName'],
            'city' => $values['city'],
            'psc' => $values['psc'],
            'country' => $values['country'],
            'isStudent' => $values['isStudent'],
            'ssVs' => $values['ssVs'],
            'schoolName' => $values['schoolName'],
            'schoolLevel' => $values['schoolLevel'],
            'schoolField' => $values['schoolField'],
            'schoolCity' => $values['schoolCity'],
            'reasons' => $values['reasons'],
            'experience' => $values['experience'],
            'profi' => $values['profi'],
            'year' => $values['year'],
            'sex' => $values['sex'],
        ]);
        if (isset($values['consens'])) {
            $conferee->consens = $values['consens'] ? new \DateTimeImmutable() : null;
        }
    }


    /**
     * @param Conferee $conferee
     * @param Form $form
     */
    public function confereeToForm(Conferee $conferee, Form $form): void
    {
        $values = $conferee->toArray();
        $values['consens'] = (bool)$conferee->consens;
        try {
            $extended = Json::decode($conferee->extended, Json::FORCE_ARRAY);
            $values['firstName'] = $extended['firstName'] ?? null;
            $values['lastName'] = $extended['lastName'] ?? null;
            $values['city'] = $extended['city'] ?? null;
            $values['psc'] = $extended['psc'] ?? null;
            $values['country'] = $extended['country'] ?? null;
            $values['isStudent'] = $extended['isStudent'] ?? null;
            $values['ssVs'] = $extended['ssVs'] ?? null;
            $values['schoolName'] = $extended['schoolName'] ?? null;
            $values['schoolLevel'] = $extended['schoolLevel'] ?? null;
            $values['schoolField'] = $extended['schoolField'] ?? null;
            $values['schoolCity'] = $extended['schoolCity'] ?? null;
            $values['reasons'] = $extended['reasons'] ?? null;
            $values['experience'] = $extended['experience'] ?? null;
            $values['profi'] = $extended['profi'] ?? null;
            $values['year'] = $extended['year'] ?? null;
            $values['sex'] = $extended['sex'] ?? null;
        } catch (JsonException $e) {
            // void
        }
        $form->setDefaults($values);
    }
}
