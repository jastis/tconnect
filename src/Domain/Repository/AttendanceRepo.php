<?php

namespace App\Domain\Repository;

use App\Factory\QueryFactory;
use DateTime;

class AttendanceRepo
{
    /**
     * @var QueryFactory The query factory
     */
    private $queryFactory;
    /**
     * The constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }


    public function createOrg(array $data): array
    {
        $result = [];
        $values = [
            'user_id' => $data['user_id'],
            'org_id' => $data['org_id'],
            'name' => $data['org_name'],
            'logo' => $data['logo'],
            'address' => $data['org_address']
            // 'phone' =>$data['phone']
        ];
        try {
            $newId = (int)$this->queryFactory->newInsert("organization", $values)
                ->execute()
                ->lastInsertId();
            $result = ['id' => $newId, 'message' => 'New Organization Created Successfully', 'Code' => 200];
            return $result;
        } catch (\Throwable $th) {
            $result = ['message' => 'New Organization Creation Failed!', 'Code' => 500];
            return $result;
        }
    }

    public function createUserGroup(array $data): array
    {
        $result = [];
        $values = [
            'aid' => $data['user_id'],
            'email' => $data['email'],
            'phone' => $data['phone'],

        ];
        try {
            $queryCheck = $this->queryFactory->newSelect('usergroup')->select(['*'])->where(['email' => $data['email']]);
            $rows = $queryCheck->execute()->fetchAll('assoc');
            if (count($rows) == 0) {
                $newId = (int)$this->queryFactory->newInsert("usergroup", $values)
                    ->execute()
                    ->lastInsertId();
                $result = ['id' => $newId, 'message' => 'New Usergroup Created Successfully', 'Code' => 200];
                return $result;
            } else {
                $result = ['message' => 'User Exist!', 'Code' => 500];
                return $result;
            }
        } catch (\Throwable $th) {
            $result = ['message' => 'New Usergroup Creation Failed!', 'Code' => 500];
            return $result;
        }
    }

    public function createEvent(array $data): array
    {
        $result = [];
        $values = [
            'event_id' => $data['event_id'],
            'event_name' => $data['event_name'],
            'org_id' => $data['org_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'event_type' => $data['event_type'],
            'place' => $data['place'] ? $data['place'] : NULL

        ];
        try {
            $newId = (int)$this->queryFactory->newInsert("event", $values)
                ->execute()
                ->lastInsertId();
            $result = ['id' => $newId, 'message' => 'New Event Created Successfully', 'Code' => 200];
            return $result;
        } catch (\Throwable $th) {
            $result = ['message' => 'New Event Creation Failed!' , 'Code' => 500];
            return $result;
        }
    }
    public function getOrg(string $user_id): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('organization')->select('*')->where(['user_id' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getAllEvents(string $user_id): array
    {
        $result = [];
        $ids = [0];
        $query = $this->queryFactory->newSelect('organization')->select(['org_id'])->where(['user_id' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');

        foreach ($rows as $row) {
            array_push($ids, $row['org_id']);
        }
        $query = $this->queryFactory->newSelect('event')->select('*')->where(['org_id IN' => $ids]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getUserGroup(string $user_id): array
    {
        $result = [];

        $query = $this->queryFactory->newSelect('usergroup')->select(['email', 'phone'])->where(['aid' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');

        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getLatestEvents(string $user_id): array
    {
        $result = [];
        $ids = [0];
        $query = $this->queryFactory->newSelect('organization')->select(['org_id'])->where(['user_id' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');

        foreach ($rows as $row) {
            array_push($ids, $row['org_id']);
        }
        $query = $this->queryFactory->newSelect('event')->select('*')->where(['org_id IN' => $ids])
            ->order(['id' => "desc"])->limit(10)->page(1);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function checkIn(array $data): array
    {
        $result = [];
        $event_owner = '';
        $event_type = 0;
        $checkerMail = '';
        $isAllowed = true;
    try {
        $queryEvent = $this->queryFactory->newSelect('event')->select('*')->where(['event_id' => $data['event']]);
        $rowEvent = $queryEvent->execute()->fetch('assoc');
        if (count($rowEvent) > 0) {
            $event_type = $rowEvent['event_type'];
            if ($event_type == 1) {
                $isAllowed = false;
                $queryOrg = $this->queryFactory->newSelect('organization')->select('user_id')->where(['org_id' => $data['org_id']]);
                $rowOrg = $queryOrg->execute()->fetch('assoc');
                if (count($rowOrg) > 0) {
                    $event_owner = $rowOrg['user_id'];
                }

                $queryChecker = $this->queryFactory->newSelect('usertbl')->select('email')->where(['user_id' => $data['user']]);
                $rowChecker = $queryChecker->execute()->fetch('assoc');
                if (count($rowChecker) > 0) {
                    $checkerMail = $rowChecker['email'];
                }
                $queryUserG = $this->queryFactory->newSelect('usergroup')->select('*')->where(['email' => $checkerMail, 'aid' => $event_owner]);
                $rowUserG = $queryUserG->execute()->fetch('assoc');
                if ($rowUserG)
                 {
                    $isAllowed = true;
                }
            }
        }
        if ($isAllowed) {

        $lnglat = explode(',', $rowEvent['place']);
        $lat1 = floatval($lnglat[0]);
        $lng1 = floatval($lnglat[1]);
        $loc = json_decode($data['location'], true);
        $lat2 = floatval($loc["coords"]["latitude"]);
        $lng2 = floatval($loc["coords"]["longitude"]);

        $dst = floatval($this->getDistanceFromEvent($lat1, $lng1, $lat2, $lng2));

        $query = $this->queryFactory->newSelect('attendance')->select('*')->andwhere([
            'user' => $data['user'],
            'org_id' => $data['org_id'], 'event_id' => $data['event'], 'DATE(time_in)' => date("Y-m-d")
        ]);
        $rowa = $query->execute()->fetch('assoc');

        $datenow  = new DateTime('now');
        $datestart = new DateTime($rowEvent['start_date']);
        $enddate = new DateTime($rowEvent['end_date']);
        $datestart_a = new DateTime($rowa['time_in']);
        $enddate_a = new DateTime($rowa['time_out']);
        $hourdiff = round((strtotime($datenow->format('Y-m-d H:i:s')) - strtotime($datestart_a->format('Y-m-d H:i:s'))) / 3600, 1);

        $this_data = [
            'org_id' => $data['org_id'],
            'event_id' => $data['event'],
            'user' => $data['user'],
            'location' => $data['location']
        ];
        
            if ($dst <= 0.5 || $rowEvent['place'] === null) {
                if (!$rowa) {
                    if ($datenow > $datestart && $enddate > $datenow) {
                        $newId = (int)$this->queryFactory->newInsert("attendance", $this_data)
                            ->execute()
                            ->lastInsertId();
                        $result = ['id' => $newId, 'message' => 'You have checked-In for '.$rowEvent['event_name'].'  attendance today. Have a great day', 'event' => $rowEvent['event_name'], 'Code' => 200];
                    } else if ($datenow > $enddate) {
                        $result = ['id' => -1, 'message' => 'The Event is closed!',  'event' => $rowEvent['event_name'], 'Code' => 500];
                        if ($datestart_a > $enddate_a) {
                            $datestart = new DateTime($rowEvent['start_date']);
                            $values = [
                                'time_out' => $datenow
                            ];
                            $this->queryFactory->newUpdate('attendance')
                                ->set($values)
                                ->andWhere(['id' => $rowa['id']])
                                ->execute();
                            $result = ['id' => $rowa['id'], 'message' => 'You have Checked-out for the day. Enjoy the rest of your day',  'event' => $rowEvent['event_name'], 'Code' => 200];
                        }
                    } else if ($datestart > $datenow) {
                        $result = ['id' => -1, 'message' => 'Event has not started!. Kindly Wait till ' . $rowEvent['start_date'], 'event' => $rowEvent['event_name'], 'Code' => 500];
                    } else {
                        $result = ['id' => -1, 'message' => 'Error Checking...!', 'event' => $rowEvent['event_name'], 'Code' => 500];
                    }
                } else if ($data['checkout']) {

                    $values = [
                        'time_out' => $datenow->format('Y-m-d H:i:s'),
                        'total_hour' => $hourdiff
                    ];
                    if ($rowa['time_out'] === null) {
                        $this->queryFactory->newUpdate('attendance')
                            ->set($values)
                            ->andWhere(['id' => $rowa['id']])
                            ->execute();
                        $result = ['id' => $rowa['id'], 'message' => 'You have Checked-out for the day. Enjoy the rest of your day',  'event' => $rowEvent['event_name'], 'Code' => 200];
                    } else {
                        $result = ['id' => -1, 'message' => 'You have closed your attendance transaction for today. Thanks',  'event' => $rowEvent['event_name'], 'Code' => 200];
                    }
                } else {
                    $result = ['id' => -1, 'message' => 'You have Checked-In today. To checkout, please press the exit icon on the top right corner (turn red) and scan again. Thanks ', 'event' => $rowEvent['event_name'], 'Code' => 500];
                }
            } else {
                $result = ['id' => -1, 'message' => 'You are outside the Authorized checking area', 'event' => $rowEvent['event_name'], 'Code' => 500];
            }
        }
        else{
            $result = ['id' => -1, 'message' => 'You are not allowed', 'event' => 'Resctricted Access', 'Code' => 500];

        }
        } catch (\Throwable $th) {
            $result = ['id' => -1, 'message' => 'Attendance Transaction Error!', 'Code' => 500];
        }
        // $result['lat'] = $dst;
        return $result;
    }

    public function getOrgs(string $userid):array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('organization')->select(['org_id']);
         $query->andWhere(['organization.user_id' => $userid ]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row['org_id'];
        }
        return $result;
    }

    public function getAttendancePreview(string $userid): array
    {
        $result = [];
        $orgs = $this->getOrgs($userid);
        $query = $this->queryFactory->newSelect('attendance')->select(['attendance.*', 'usertbl.first_name', 'usertbl.last_name','organization.name'])
            ->innerjoin('usertbl', 'usertbl.user_id = attendance.user')
            ->innerjoin('organization', 'organization.org_id = attendance.org_id');    
        $query->andWhere(['attendance.org_id IN' => $orgs, 'date(attendance.time_in)' => date('Y-m-d')]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }


    public function getAttendanceRange(array $data): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('attendance')->select(['attendance.*', 'usertbl.first_name', 'usertbl.last_name'])
            ->innerjoin('usertbl', 'usertbl.user_id = attendance.user');
        $query->andWhere(['attendance.event_id' => $data['event_id'] ? $data['event_id'] : 0, 'attendance.org_id' => $data['org_id'] ? $data['org_id'] : 0, $query->newExpr()->between('attendance.time_in', $data['start_date'], $data['end_date']),]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

   
    public function getAttendanceRating(string $userid): array
    {

        $result = [];
        $orgs = $this->getOrgs($userid);
        $dt = date('Y-m-d');
       $start_date =  date("Y-m-01", strtotime($dt));
       $end_date   =  date("Y-m-t", strtotime($dt)); 
        
        $query = $this->queryFactory->newSelect('attendance');
        $query->select(['total_hours' => $query->func()->sum('attendance.total_hour'), 'first_name'=>'ANY_VALUE(usertbl.first_name)', 'last_name'=>'ANY_VALUE(usertbl.last_name)'])
            ->innerjoin('usertbl', 'usertbl.user_id = attendance.user');
        $query->andWhere([ 'attendance.org_id IN' => $orgs, $query->newExpr()->between('attendance.time_in', $start_date, $end_date),]);
        $query->group(['usertbl.user_id']);
        $query->order('total_hours','DESC')->limit(3);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getAttendanceTrends(string $userid): array
    {

        $result = [];
        $orgs = $this->getOrgs($userid);
        $dt = date('Y-m-d');
        $firstDayOfYear = mktime(0, 0, 0, 1, 1, date("2022"));
       $start_date =  date("Y-m-d", $firstDayOfYear);;
       $end_date   =  date("Y-m-t", strtotime($dt)); 
        
        $query = $this->queryFactory->newSelect('attendance');
        $query->select(['total_hours' => $query->func()->sum('attendance.total_hour'), 'atDay'=>'date(attendance.time_in)','total_user'=>$query->func()->count('attendance.user') ]);
        $query->andWhere([ 'attendance.org_id IN' => $orgs, $query->newExpr()->between('attendance.time_in', $start_date, $end_date),]);
        $query->group(['atDay']);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result['hours'][] =$row['total_hours'];
            $result['days'][] =$row['atDay'];
            $result['users'][] =$row['total_user'];
        }
        return $result;
    }

    public function getAttendanceSummaryRange(array $data): array
    {

        $result = [];
        $query = $this->queryFactory->newSelect('attendance');
        $query->select(['total_hours' => $query->func()->sum('attendance.total_hour'),'first_name'=>'ANY_VALUE(usertbl.first_name)', 'last_name'=>'ANY_VALUE(usertbl.last_name)'])
            ->innerjoin('usertbl', 'usertbl.user_id = attendance.user');
        $query->andWhere(['attendance.event_id' => $data['event_id'] ? $data['event_id'] : 0, 'attendance.org_id' => $data['org_id'] ? $data['org_id'] : 0, $query->newExpr()->between('attendance.time_in', $data['start_date'], $data['end_date']),]);
        $query->group(['usertbl.user_id']);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getTemplateWithToken(string $token): array
    {
        $result = ['template_code' => 0];
        $query = $this->queryFactory->newSelect('custom_template')->select(['template_code'])->where(['design_code' => $token]);
        $row = $query->execute()->fetch('assoc');
        $result = $row ? $row : $result;
        return $result;
    }

    private function getDistanceFromEvent($lat1, $lng1, $lat2, $lng2): float
    {


        $radius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1))
            * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * asin(sqrt($a));
        $d = $radius * $c;
        return $d;
    }
}
