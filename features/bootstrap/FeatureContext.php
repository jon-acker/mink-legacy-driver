<?php

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawMinkContext
{
    /**
     * @Given AAA :arg1 :arg2
     */
    public function aaa2($name, $surname)
    {
        $this->getSession()->visit('/form');

//        echo $this->getSession()->getPage()->getContent();die;

        $this->getSession()->getPage()->fillField('name', $name);
        $this->getSession()->getPage()->fillField('surname', $surname);
        $this->getSession()->getPage()->pressButton('send');

        echo $this->getSession()->getPage()->getContent();
    }

    /**
     * @When BBB
     */
    public function bbb()
    {
    }

    /**
     * @Then CCC
     */
    public function ccc()
    {
    }
}
