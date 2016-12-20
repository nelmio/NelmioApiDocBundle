<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ValidatorTest
{
    /**
     * @Assert\Length(min=10);
     */
    public $length10 = 'validate this';

    /**
     * @Assert\Length(min=1, max=10)
     */
    public $length1to10;

    /**
     * @Assert\NotBlank()
     */
    public $notblank;

    /**
     * @Assert\NotNull()
     */
    public $notnull;

    /**
     * @Assert\Type("DateTime");
     */
    public $type;

    /**
     * @Assert\Date();
     */
    public $date;

    /**
     * @Assert\DateTime();
     */
    public $dateTime;

    /**
     * @Assert\Time();
     */
    public $time;

    /**
     * @Assert\Email()
     */
    public $email;

    /**
     * @Assert\Url()
     */
    public $url = 'https://github.com';

    /**
     * @Assert\Ip()
     */
    public $ip;

    /**
     * @Assert\Choice(choices={"a", "b"})
     */
    public $singlechoice;

    /**
     * @Assert\Choice(choices={"x", "y", "z"}, multiple=true)
     */
    public $multiplechoice;

    /**
     * @Assert\Choice(choices={"foo", "bar", "baz", "qux"}, multiple=true, min=2, max=3)
     */
    public $multiplerangechoice;

    /**
     * @Assert\Regex(pattern="/^\d{1,4}\w{1,4}$/")
     */
    public $regexmatch;

    /**
     * @Assert\Regex(pattern="/\d/", match=false)
     */
    public $regexnomatch;

    /**
     * @Assert\NotNull()
     * @Assert\Type("string")
     * @Assert\Email()
     */
    public $multipleassertions;

    /**
     * @Assert\Url()
     * @Assert\Length(min=10)
     */
    public $multipleformats;
}
