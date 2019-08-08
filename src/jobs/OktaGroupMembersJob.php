<?php

namespace NZTA\OktaAPI\jobs;

use Exception;
use NZTA\OktaAPI\model\OktaUserGroupFilter;
use NZTA\OktaAPI\Services\OktaService;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\Queries\SQLUpdate;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;

class OktaGroupMembersJob extends AbstractQueuedJob implements QueuedJob
{

    /**
     * Time in seconds to schedule for, from when SyncOktaUsersJob job finishes.
     *
     * @var integer
     */
    public $schedule_after = 30;

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Get Okta Users and update DisplayProfile';
    }

    /**
     * We get all the user group filters which is assign in the CMS and
     * get all the Okta members belongs to those groups
     * and update the Member DisplayProfile to true
     *
     * Marking as complete so it can be removed from the queue
     *
     * @return void
     */
    public function process()
    {
        $filters = OktaUserGroupFilter::get();

        $usersCount = 0;
        $allUsers = [];
        foreach ($filters as $filter) {
            if ($groupID = $filter->OktaGroupID) {
                $users = Injector::inst()
                    ->get(OktaService::class)
                    ->getAllUsersFromGroup(100, $groupID);
                $allUsers = array_merge($allUsers, $users);

                $usersCount += count($users);
            }
        }

        try {
            // Set all users DisplayProfile to 0 before run the job
            SQLUpdate::create('"Member"')
                ->assign('"DisplayProfile"', '0')
                ->execute();
        } catch (Exception $e) {
            $this->getLogger()->error(
                sprintf(
                    'Error occurred attempting to update users DisplayProfile to 0 in OktaGroupMembersJob. %s',
                    $e->getMessage()
                )
            );
        }

        $this->updateUsers($allUsers);

        // add a message to the job to show number of Members updated DisplayProfile in the CMS
        $this->addMessage(sprintf(
            'updated %d users',
            $usersCount
        ));

        $this->markJobAsDone();
    }

    /**
     * Create a single UPDATE statement to update existing Member records
     * with DisplayProfile
     *
     * @param array $users
     *
     * @return void
     */
    private function updateUsers(array $users)
    {
        if (count($users) > 0) {
            // Get all okta ids to an array
            $oktaIDs = array_column($users, 'id');

            if (count($oktaIDs) == 0) {
                return;
            }

            // create the sql update statement
            $update = SQLUpdate::create('"Member"')->assign('"DisplayProfile"', '1')
                ->setWhere(sprintf("OktaID IN ('%s')", implode("','", $oktaIDs)));

            try {
                // run the UPDATE statement
                $update->execute();
            } catch (Exception $e) {
                $this->getLogger()->error(
                    sprintf(
                        'Error occurred attempting to update users in OktaGroupMembersJob. %s',
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * Complete the job so it can removed from the queue
     *
     * @return void
     */
    private function markJobAsDone()
    {
        $this->totalSteps = 0;
        $this->isComplete = true;
    }

    /**
     * Get a logger
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return Injector::inst()->get(LoggerInterface::class);
    }
}
