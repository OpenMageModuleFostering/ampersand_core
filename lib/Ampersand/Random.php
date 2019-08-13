<?php
class Ampersand_Random
{
    /** @var array */
    protected static $_firstnames = array(
        'Oliver',
        'Jack',
        'Harry',
        'Alfie',
        'Charlie',
        'Thomas',
        'William',
        'Joshua',
        'George',
        'James',
        'Daniel',
        'Jacob',
        'Ethan',
        'Samuel',
        'Joseph',
        'Dylan',
        'Mohammed',
        'Noah',
        'Lucas',
        'Oscar',
        'Alexander',
        'Benjamin',
        'Max',
        'Archie',
        'Riley',
        'Jayden',
        'Lewis',
        'Logan',
        'Jake',
        'Ryan',
        'Muhammad',
        'Tyler',
        'Liam',
        'Henry',
        'Finley',
        'Leo',
        'Isaac',
        'Luke',
        'Adam',
        'Callum',
        'Olivia',
        'Sophie',
        'Emily',
        'Lily',
        'Amelia',
        'Jessica',
        'Ruby',
        'Chloe',
        'Grace',
        'Evie',
        'Ava',
        'Isabella',
        'Mia',
        'Maisie',
        'Daisy',
        'Poppy',
        'Isabelle',
        'Ella',
        'Freya',
        'Charlotte',
        'Lucy',
        'Isla',
        'Megan',
        'Scarlett',
        'Holly',
        'Imogen',
        'Sophia',
        'Phoebe',
        'Ellie',
        'Summer',
        'Hannah',
        'Millie',
        'Lola',
        'Abigail',
        'Erin',
        'Lacey',
        'Eva',
        'Amy',
        'Lilly',
        'Katie',
    );
    
    /** @var array */
    protected static $_lastnames = array(
        'Smith',
        'Jones',
        'Taylor',
        'Brown',
        'Williams',
        'Wilson',
        'Johnson',
        'Davies',
        'Robinson',
        'Wright',
        'Thompson',
        'Evans',
        'Walker',
        'White',
        'Roberts',
        'Green',
        'Hall',
        'Wood',
        'Jackson',
        'Clarke',
        'Brown',
        'Smith',
        'Patel',
        'Jones',
        'Williams',
        'Johnson',
        'Taylor',
        'Thomas',
        'Roberts',
        'Khan',
        'Lewis',
        'Jackson',
        'Clarke',
        'James',
        'Phillips',
        'Wilson',
        'Ali',
        'Mason',
        'Mitchell',
        'Rose',
        'Davis',
        'Davies',
        'Rodríguez',
        'Cox',
        'Alexander',
    );
    
    /** @var array */
    protected static $_emailDomains = array(
        'hotmail.com',
        'hotmail.co.uk',
        'gmail.com',
        'yahoo.com',
    );
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function getFirstname()
    {
        $key = array_rand(self::$_firstnames);
        
        return self::$_firstnames[$key];
    }
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function getLastname()
    {
        $key = array_rand(self::$_lastnames);
        
        return self::$_lastnames[$key];
    }
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function getEmailDomain()
    {
        $key = array_rand(self::$_emailDomains);
        
        return self::$_emailDomains[$key];
    }
    
    /**
     * @param string $include OPTIONAL
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function getEmail($include = null)
    {
        $name = self::getFirstname() . '.' . self::getLastname();
        
        return ($name . $include . '@' . self::getEmailDomain());
    }
}