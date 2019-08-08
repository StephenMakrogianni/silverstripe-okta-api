<?php

namespace NZTA\OktaAPI\Model;

use SilverStripe\ORM\DataObject;

class OktaUserGroupFilter extends DataObject
{
    /**
     * @var string
     */
    private static $singular_name = 'Okta user group filter';

    /**
     * @var string
     */
    private static $plural_name = 'Okta user group filters';

    /**
     * @var string
     */
    private static $table_name = 'OktaUserGroupFilter';

    /**
     * @var array
     */
    private static $db = [
        'OktaGroupID' => 'Varchar(255)',
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'OktaGroupID',
    ];
}
