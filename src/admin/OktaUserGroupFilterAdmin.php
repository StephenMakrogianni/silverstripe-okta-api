<?php

namespace NZTA\OktaAPI\Admin;

use NZTA\OktaAPI\Model\OktaUserGroupFilter;
use SilverStripe\Admin\ModelAdmin;

class OktaUserGroupFilterAdmin extends ModelAdmin
{

    /**
     * @var string
     */
    private static $singular_name = 'Okta user group filter model admin page';

    /**
     * @var string
     */
    private static $plural_name = 'Okta user group filter model admin pages';

    /**
     * @var string
     */
    private static $table_name = 'OktaUserGroupFilterAdmin';

    /**
     * @var string
     */
    private static $menu_title = 'Okta User Group Filters';

    /**
     * @var string
     */
    private static $url_segment = 'oktausergroupfilters';

    /**
     * @var array
     */
    private static $managed_models = [
        OktaUserGroupFilter::class,
    ];
}
