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


    public function createOrg(array $data):array{
        $result = [];
       $values =[
           'user_id'=>$data['user_id'],
           'org_id' =>$data['org_id'],
           'name'=>$data['org_name'],
           'logo'=>$data['logo'],
           'address'=>$data['org_address']
          // 'phone' =>$data['phone']
       ];
       try {
        $newId = (int)$this->queryFactory->newInsert("organization", $values)
        ->execute()
        ->lastInsertId();
        $result = ['id'=>$newId, 'message' => 'New Organization Created Successfully', 'Code' => 200];
        return $result;
       } catch (\Throwable $th) {
        $result = [ 'message' => 'New Organization Creation Failed!', 'Code' => 500];
        return $result;
       }
      
    }

    public function createEvent(array $data):array{
        $result = [];
       $values =[
           'event_id'=>$data['event_id'],
           'event_name' =>$data['event_name'],
           'org_id'=>$data['org_id'],
           'start_date'=>$data['start_date'],
           'end_date'=>$data['end_date'],
            'place'=>$data['place']
          
       ];
       try {
        $newId = (int)$this->queryFactory->newInsert("event", $values)
        ->execute()
        ->lastInsertId();
        $result = ['id'=>$newId, 'message' => 'New Event Created Successfully', 'Code' => 200];
        return $result;
       } catch (\Throwable $th) {
        $result = [ 'message' => 'New Event Creation Failed!', 'Code' => 500];
        return $result;
       }
      
    }
 public function getOrg(string $user_id):array{
    $result = [];
    $query = $this->queryFactory->newSelect('organization')->select('*')->where(['user_id' => $user_id]);
    $rows = $query->execute()->fetchAll('assoc');
    foreach ($rows as $row) {
        $result[] = $row;
    }
    return $result;
 }

 public function getAllEvents(string $user_id):array{
    $result = [];
    $ids = [0];
    $query = $this->queryFactory->newSelect('organization')->select(['org_id'])->where(['user_id' => $user_id]);
    $rows = $query->execute()->fetchAll('assoc');

    foreach ($rows as $row) {
        array_push($ids,$row['org_id']);
    }
    $query = $this->queryFactory->newSelect('event')->select('*')->where(['org_id IN' => $ids]);
    $rows = $query->execute()->fetchAll('assoc');
    foreach ($rows as $row) {
        $result[] = $row;
    }
    return $result;
 }

 public function getLatestEvents(string $user_id):array{
    $result = [];
    $ids = [0];
    $query = $this->queryFactory->newSelect('organization')->select(['org_id'])->where(['user_id' => $user_id]);    
    $rows = $query->execute()->fetchAll('assoc');

    foreach ($rows as $row) {
        array_push($ids,$row['org_id']);
    }
    $query = $this->queryFactory->newSelect('event')->select('*')->where(['org_id IN' => $ids])
    ->order(['id'=> "desc"])->limit(10)->page(1);
    $rows = $query->execute()->fetchAll('assoc');
    foreach ($rows as $row) {
        $result[] = $row;
    }
    return $result;
 }
 
    public function checkIn(array $data): array
    {
        $result = [];
        

        $query = $this->queryFactory->newSelect('event')->select('*')->where(['event_id' => $data['event']]);
        $row = $query->execute()->fetch('assoc');
        $lnglat = explode(',',$row['place']);
        $lat1 = floatval($lnglat[0]);
        $lng1 = floatval($lnglat[1]);
        $loc = json_decode($data['location'],true);
        $lat2 = floatval($loc["coords"]["latitude"]);
        $lng2 = floatval($loc["coords"]["longitude"]);

        $dst = floatval( $this->getDistanceFromEvent($lat1,$lng1,$lat2,$lng2));
       
        $query = $this->queryFactory->newSelect('attendance')->select('*')->andwhere([
            'user' => $data['user'],
            'org_id' => $data['org_id'], 'event_id' => $data['event'], 'DATE(time_in)'=> date("Y-m-d")
        ]);
        $rowa = $query->execute()->fetch('assoc');

        $datenow  = new DateTime('now');
        $datestart = new DateTime($row['start_date']);
        $enddate = new DateTime($row['end_date']);
        $datestart_a = new DateTime($rowa['time_in']);
        $enddate_a = new DateTime($rowa['time_out']);
        $hourdiff = round((strtotime($datenow->format('Y-m-d H:i:s')) - strtotime($datestart_a->format('Y-m-d H:i:s')))/3600, 1);

        $this_data = [
            'org_id' => $data['org_id'],
            'event_id' => $data['event'],
            'user' => $data['user'],
            'location' => $data['location']
        ];
        try {
            if ($dst <= 0.5 || $row['place'] === null) {
            if (!$rowa) {
                if ($datenow > $datestart && $enddate > $datenow) {
                    $newId = (int)$this->queryFactory->newInsert("attendance", $this_data)
                        ->execute()
                        ->lastInsertId();
                    $result = ['id' => $newId, 'message' => 'You have checked-In for attendance today. Have a great day', 'event' => $row['event_name'], 'Code' => 200];
                } else if ($datenow > $enddate) {
                    $result = ['id' => -1, 'message' => 'The Event is closed!',  'event' => $row['event_name'], 'Code' => 500];
                    if ($datestart_a > $enddate_a) {
                        $datestart = new DateTime($row['start_date']);
                        $values = [
                            'time_out' => $datenow
                        ];
                        $this->queryFactory->newUpdate('attendance')
                            ->set($values)
                            ->andWhere(['id' => $rowa['id']])
                            ->execute();
                        $result = ['id' => $rowa['id'], 'message' => 'You have Checked-out for the day. Enjoy the rest of your day',  'event' => $row['event_name'], 'Code' => 200];
                    }
                } else if ($datestart > $datenow) {
                    $result = ['id' => -1, 'message' => 'Event has not started!. Kindly Wait till ' . $row['start_date'], 'event' => $row['event_name'], 'Code' => 500];
                } else {
                    $result = ['id' => -1, 'message' => 'Error Checking...!', 'event' => $row['event_name'], 'Code' => 500];
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
                        $result = ['id' => $rowa['id'], 'message' => 'You have Checked-out for the day. Enjoy the rest of your day',  'event' => $row['event_name'], 'Code' => 200];
                } else {
                    $result = ['id' => -1, 'message' => 'You have closed your attendance transaction for today. Thanks',  'event' => $row['event_name'], 'Code' => 200];
                }
            } else {
                $result = ['id' => -1, 'message' => 'You have Checked-In today. To checkout, please press the exit icon on the top right corner (turn red) and scan again. Thanks ', 'event' => $row['event_name'], 'Code' => 500];
            }
        }else{
            $result = ['id' => -1, 'message' => 'You are outside the Authorized checking area', 'event' => $row['event_name'], 'Code' => 500]; 
        }
        } catch (\Throwable $th) {
            $result = ['id' => -1, 'message' => 'Attendance Transaction Error!' , 'Code' => 500];
        }
        // $result['lat'] = $dst;
        return $result;
    }

    public function getAttendanceRange(array $data): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('attendance')->select(['attendance.*', 'usertbl.first_name','usertbl.last_name'])
        ->innerjoin('usertbl', 'usertbl.user_id = attendance.user');
        $query->andWhere(['attendance.event_id'=>$data['event_id']?$data['event_id']:0,'attendance.org_id   '=>$data['org_id']?$data['org_id']:0, $query->newExpr()->between('attendance.time_in', $data['start_date'], $data['end_date']),]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }
    public function getAttendanceSummaryRange(array $data): array
    {
        
        $result = [];
        $query = $this->queryFactory->newSelect('attendance');
        $query->select(['total_hours' => $query->func()->sum('attendance.total_hour'), 'usertbl.first_name','usertbl.last_name'])
        ->innerjoin('usertbl', 'usertbl.user_id = attendance.user');
        $query->andWhere(['attendance.event_id'=>$data['event_id']?$data['event_id']:0,'attendance.org_id'=>$data['org_id']?$data['org_id']:0, $query->newExpr()->between('attendance.time_in', $data['start_date'], $data['end_date']),]);
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

    private function getDistanceFromEvent($lat1,$lng1,$lat2, $lng2):float{

       
        $radius = 6371;
    
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lng2 - $lng1);
    
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) 
                 * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        $d = $radius * $c;
        return $d;
    }
}
