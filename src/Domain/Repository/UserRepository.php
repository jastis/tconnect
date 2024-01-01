<?php

namespace App\Domain\Repository;

use App\Factory\QueryFactory;


class UserRepository
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

    public function CreateNewUser(string $table, array $data): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('usertbl')->select(['email'])->where(['OR' => [['email' => $data['email']], ['phone_no' => $data['phone_no']]]]);
        $rows = $query->execute()->fetchAll('assoc');
        if (count($rows) > 0) {
            $result = ['id' => 0, 'description' => 'User with email or Phone_no exist!'];
            return $result;
        }
        $this_data = [
            'user_id' => $data['userid'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone_no' => $data['phone_no'],
            'email' => $data['email'],
            'usertype' => 1,
            'subscription' => 1,
            'status' => 0,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'refcode' => $data['refcode']
        ];
        $newId = (int)$this->queryFactory->newInsert($table, $this_data)
            ->execute()
            ->lastInsertId();

        $result = ['id' => $newId, 'description' => 'User Created Successfully!'];
        return $result;
    }



    public function addProfile(array $data): array
    {
        $result = [];

        $this_data = [
            'user_id' => $data['user_id'],
            'prefix' => isset($data['prefix']) ? $data['prefix'] : null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => isset($data['middle_name']) ? $data['middle_name'] : null,
            'suffix' => isset($data['suffix']) ? $data['suffix'] : null,
            'organization' => isset($data['organization']) ? $data['organization'] : null,
            'photo' => isset($data['photo']) ? $data['photo'] : null,
            'workphone' => isset($data['workphone']) ? $data['workphone'] : null,
            'cellphone' => $data['cellphone'],
            'title' => isset($data['title']) ? $data['title'] : null,
            'url' => isset($data['url']) ? $data['url'] : null,
            'note' => isset($data['note']) ? $data['note'] : null,
            'logo' => isset($data['logo']) ? $data['logo'] : null,
            'email' => $data['email'],
            'workemail' => isset($data['workemail']) ? $data['workemail'] : null,
            'role' => $data['role'],
            'street' => $data['street'],
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'facebook' => isset($data['facebook']) ? $data['facebook'] : null,
            'linkedin' => isset($data['linkedin']) ? $data['linkedin'] : null,
            'twitter' => isset($data['twitter']) ? $data['twitter'] : null,
            'instagram' => isset($data['instagram']) ? $data['instagram'] : null,
            'whatsapp' => isset($data['whatsapp']) ? $data['whatsapp'] : null,
            'usertype' => 1, //$data['usertype'],
            'subscription' => 0, //$data['subscription'],
            'status' => 1, //$data['status'],
            'template' => (int)$data['template']

        ];

        $querytheme = $this->queryFactory->newSelect('themes')->select(['subscription'])
            ->andWhere(['id' => $data['template']]);
        $rowtheme = $querytheme->execute()->fetch('assoc');

        $max_user = 0;
        $cur_card = 0;
        if ($rowtheme['subscription'] == 0) { // free
            $max_user = 1;
            $querypro = $this->queryFactory->newSelect('profile');
            $querypro->select(['cards' => $querypro->func()->count('id')])
                ->andWhere(['template' => $data['template'], 'user_id' => $data['user_id']]);
            $rowpro = $querypro->execute()->fetch('assoc');
            $cur_card = $rowpro['cards'];
        } else if ($rowtheme['subscription'] == 1) { //paid
            $querysub = $this->queryFactory->newSelect('subscription')->select(['max_user'])
                ->andWhere(['template' => $data['template'], 'user_id' => $data['user_id']]);
            $rowsub = $querysub->execute()->fetch('assoc');
            $max_user =  (int) $rowsub['max_user'];

            $querypro = $this->queryFactory->newSelect('profile');
            $querypro->select(['cards' => $querypro->func()->count('id')])
                ->andWhere(['template' => $data['template'], 'user_id' => $data['user_id']]);
            $rowpro = $querypro->execute()->fetch('assoc');
            $cur_card = $rowpro['cards'];
        } else if ($rowtheme['subscription'] == 2) { // custom
            $querysub = $this->queryFactory->newSelect('subscription')->select(['max_user'])
                ->andWhere(['template' => $data['template']]);
            $rowsub = $querysub->execute()->fetch('assoc');
            $max_user =  (int) $rowsub['max_user'];

            $querypro = $this->queryFactory->newSelect('profile');
            $querypro->select(['cards' => $querypro->func()->count('id')])
                ->andWhere(['template' => $data['template']]);
            $rowpro = $querypro->execute()->fetch('assoc');
            $cur_card = (int) $rowpro['cards'];
        }

        if ($cur_card < $max_user) {
            $newId = (int)$this->queryFactory->newInsert("profile", $this_data)
                ->execute()
                ->lastInsertId();
            $query = $this->queryFactory->newSelect('profile')->select(['profile.*', 'temptheme' => 'themes.theme'])
                ->innerjoin('themes', 'themes.id = profile.template')
                ->where(['profile.id' => $newId]);
            $row = $query->execute()->fetch('assoc');
            $result = $row ? $row : $result;
            $result['temptheme'] = $row ? json_decode($row['temptheme'], true, JSON_UNESCAPED_SLASHES) : [];
        }
        return $result;
    }


    public function editProfile(array $data): array
    {
        $this_data = [
            'user_id' => $data['user_id'],
            'prefix' => isset($data['prefix']) ? $data['prefix'] : null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => isset($data['middle_name']) ? $data['middle_name'] : null,
            'suffix' => isset($data['suffix']) ? $data['suffix'] : null,
            'organization' => $data['organization'],
            'photo' => isset($data['photo']) ? $data['photo'] : null,
            'workphone' => isset($data['workphone']) ? $data['workphone'] : null,
            'cellphone' => $data['cellphone'],
            'title' => isset($data['title']) ? $data['title'] : null,
            'url' => isset($data['url']) ? $data['url'] : null,
            'note' => isset($data['note']) ? $data['note'] : null,
            'logo' => isset($data['logo']) ? $data['logo'] : null,
            'email' => $data['email'],
            'workemail' => isset($data['workemail']) ? $data['workemail'] : null,
            'role' => $data['role'],
            'street' => $data['street'],
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'facebook' => isset($data['facebook']) ? $data['facebook'] : null,
            'linkedin' => isset($data['linkedin']) ? $data['linkedin'] : null,
            'twitter' => isset($data['twitter']) ? $data['twitter'] : null,
            'instagram' => isset($data['instagram']) ? $data['instagram'] : null,
            'usertype' => $data['usertype'],
            'subscription' => $data['subscription'],
            'status' => $data['status'],

        ];
        $this->queryFactory->newUpdate('profile')
            ->set($this_data)
            ->andWhere(['id' => $data['id']])
            ->execute();
        $result = ['description' => 'Profile Updated Successfully'];
        return $result;
    }


    public function addOtherProfile(array $data, string $uid): array
    {
        $found = true;
        $template = 0;
        $query = $this->queryFactory->newSelect('cards')->select('*')->where(['user_id' => $uid, 'from_user' => $data[0], 'pro_id' => (int)$data[1]]);
        $row = $query->execute()->fetch('assoc');
        if (!$row) {
            $found = false;
        }

        $query = $this->queryFactory->newSelect('profile')->select('*')->where(['user_id' => $data[0], 'id' => $data[1]]);
        $row = $query->execute()->fetch('assoc');
        $result = $row ? $row : [];
        if ($result) {
            $template = $row['template'];
            if (!$found) {
                $values = [
                    'user_id' => $uid,
                    'from_user' => $data[0],
                    'pro_id' => $data[1]
                ];
                $newId = (int)$this->queryFactory->newInsert("cards", $values)
                    ->execute()
                    ->lastInsertId();
            }
            $query = $this->queryFactory->newSelect('themes')->select(['subscription'])->where(['id' => $template]);
            $rowsub = $query->execute()->fetch('assoc');

            $sub_status = $rowsub['subscription'];
            if ($sub_status == 1) {
                $query = $this->queryFactory->newSelect('subscription')->select(['id'])
                    ->where(['user_id' => $data[0], 'template' => $template, 'status' => 1]);
                $chksub = $query->execute()->fetch('assoc');
                if (!$chksub) {
                    $result['subscribe'] = false;
                    return $result;
                }
            } else if ($sub_status == 2) {
                $query = $this->queryFactory->newSelect('subscription')->select(['id'])
                    ->where(['template' => $template, 'status' => 1]);
                $chksub = $query->execute()->fetch('assoc');
                if (!$chksub) {
                    $result['subscribe'] = false;
                    return $result;
                }
            }

            $query = $this->queryFactory->newSelect('themes')->select(['theme'])->where(['id' => $row['template']]);
            $rowtheme = $query->execute()->fetch('assoc');
            $result['temptheme'] = $rowtheme ? json_decode($rowtheme['theme'], true, JSON_UNESCAPED_SLASHES) : [];
            if ($result['temptheme']) {
                $result['subscribe'] = true;
            }
        }
        return $result;
    }

    public function getProfile(string $user_id): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('profile')->select(['profile.*', 'temptheme' => 'themes.theme'])
            ->innerjoin('themes', 'themes.id = profile.template')
            ->where(['user_id' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $row['temptheme'] =  $row['temptheme'] ? json_decode($row['temptheme'], true, JSON_UNESCAPED_SLASHES) : null;
            $result[] = $row;
        }
        $pros = [0];
        $query = $this->queryFactory->newSelect('cards')->select(['pro_id'])
            ->where(['user_id' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            array_push($pros, $row['pro_id']);
        }
        $query = $this->queryFactory->newSelect('profile')->select(['profile.*', 'usertbl.token', 'temptheme' => 'themes.theme'])
            ->innerjoin('usertbl', 'usertbl.user_id = profile.user_id')
            ->innerjoin('themes', 'themes.id = profile.template')
            ->where(['profile.id IN' => $pros]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $row['temptheme'] =  $row['temptheme'] ? json_decode($row['temptheme'], true, JSON_UNESCAPED_SLASHES) : null;
            $result[] = $row;
        }
        return $result;
    }
    public function getAllProfile(string $user_id): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('profile')->select(['profile.*', 'temptheme' => 'themes.theme'])
            ->innerjoin('themes', 'themes.id = profile.template')
            ->where(['user_id' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $row['temptheme'] =  $row['temptheme'] ? json_decode($row['temptheme'], true, JSON_UNESCAPED_SLASHES) : null;
            $result[] = $row;
        }

        $pros = [0];
        $query = $this->queryFactory->newSelect('cards')->select(['pro_id'])
            ->where(['user_id' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            array_push($pros, $row['pro_id']);
        }
        $query = $this->queryFactory->newSelect('profile')->select(['profile.*', 'usertbl.token', 'temptheme' => 'themes.theme'])
            ->innerjoin('usertbl', 'usertbl.user_id = profile.user_id')
            ->innerjoin('themes', 'themes.id = profile.template')
            ->where(['profile.id IN' => $pros]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $row['temptheme'] =  $row['temptheme'] ? json_decode($row['temptheme'], true, JSON_UNESCAPED_SLASHES) : null;
            $result[] = $row;
        }
        return $result;
    }

    public function countAllProfile(): int
    {
        $result = 0;
        $query = $this->queryFactory->newSelect('profile')->select(['profile.id']);
        $rows = $query->execute()->fetchAll('assoc');
        $result = count($rows);
        return $result;
    }

    public function getAccountStatus(string $email): int
    {
        $result = 2;

        $query = $this->queryFactory->newSelect('usertbl')->select(['usertbl.status'])
            ->where(['usertbl.email' => $email]);
        $row = $query->execute()->fetch('assoc');
        $result = $row['status'] ? (int) $row['status'] : 2;
        return $result;
    }

    public function countAllProfileByUser(string $user_id): int
    {
        $result = 0;
        $query = $this->queryFactory->newSelect('profile')->select(['profile.id'])
            ->innerjoin('themes', 'themes.id = profile.template')
            ->where(['user_id' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        $result = count($rows);
        return $result;
    }
    public function removeProfile(string $user_id, int $cid): array
    {
        $this->queryFactory->newDelete('profile')
            ->andWhere(['id' => $cid, 'user_id' => $user_id])
            ->execute();

        $result = ['message' => "Deleted"];
        //     $query = $this->queryFactory->newSelect('profile')->select('*')->where(['user_id'=>$user_id]);
        //     $rows = $query->execute()->fetchAll('assoc');
        //    foreach($rows as $row){
        //         $result[] = $row;
        //     }
        //     $pros = [0];
        //     $query = $this->queryFactory->newSelect('cards')->select(['pro_id'])
        //     ->where(['user_id'=>$user_id]);
        //     $rows = $query->execute()->fetchAll('assoc');
        //    foreach($rows as $row){
        //         array_push($pros,$row['pro_id']);
        //     }
        //     $query = $this->queryFactory->newSelect('profile')->select('*')->where(['id IN'=>$pros]);
        //     $rows = $query->execute()->fetchAll('assoc');
        //    foreach($rows as $row){
        //         $result[] = $row;
        //    }
        return $result;
    }

    public function removeUserByUserID(string $user_id): array
    {


        $this->queryFactory->newDelete('usertbl')
            ->Where(['user_id' => $user_id])
            ->execute();

        $result = ['message' => " User Account Deleted"];

        return $result;
    }

    public function removeCard(array $data): array
    {
        $this->queryFactory->newDelete('cards')
            ->andWhere(['pro_id' => $data['pro_id'], 'user_id' => $data['uid'], 'from_user' => $data['from_user']])
            ->execute();

        $result = [];
        $query = $this->queryFactory->newSelect('profile')->select('*')->where(['user_id' => $data['uid']]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        $pros = [0];
        $query = $this->queryFactory->newSelect('cards')->select(['pro_id'])
            ->where(['user_id' => $data['uid']]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            array_push($pros, $row['pro_id']);
        }
        $query = $this->queryFactory->newSelect('profile')->select('*')->where(['id IN' => $pros]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getUser(array $data): array
    {
        $result = [];
        $value =
            [
                'email' => $data['email'],
                'cellphone' => $data['email'],
            ];
        $query = $this->queryFactory->newSelect('usertbl')
            ->select('*')
            ->Where(['OR' => [['email' => $data['email']], ['phone_no' => $data['email']]]]);
        $rows = $query->execute()->fetch('assoc');
        if ($rows) {
            if ($rows['status'] == 1) {
                $hashpass = $rows['password'];
                $user = $rows;
                if (password_verify($data['password'], $hashpass)) {
                    unset($user['password']);
                    $result = ['user' => $user, 'found' => true];
                    return $result;
                } else {
                    $result = [
                        'found' => false,
                        'message' => 'Incorrect Password!'
                    ];
                    return $result;
                }
            } else {
                $result = [
                    'found' => false,
                    'message' => 'Account not Activated'
                ];
                return $result;
            }
        }
        $result = [
            'found' => false,
            'message' => 'Incorrect Email'
        ];
        return $result;
    }

    public function getAppUser(array $data): array
    {
        $result = [];
        $value =
            [
                'email' => $data['email'],
                'phone_no' => $data['email'],
            ];
        $query = $this->queryFactory->newSelect('usertbl')
            ->select('*')
            ->Where(['OR' => [['email' => $data['email']], ['phone_no' => $data['email']]]]);
        $row = $query->execute()->fetch('assoc');
        if ($row) {
            if ($row['status'] == 1) {
                $hashpass = $row['password'];
                $user = $row;
                $user['pushToken'] = $data['pushTokens'];
                if (password_verify($data['password'], $hashpass)) {
                    //unset($user['password']);
                    $result = ['user' => $user, 'found' => true];
                    $loginToken = ['token' => $data['pushTokens']];
                    $this->queryFactory->newUpdate('usertbl')
                        ->set($loginToken)
                        ->andWhere(['OR' => [['email' => $data['email']], ['phone_no' => $data['email']]]])
                        ->execute();
                    return $result;
                } else {
                    $result = [
                        'found' => false,
                        'message' => 'Incorrect Password!'
                    ];
                    return $result;
                }
            } else {
                $result = [
                    'found' => false,
                    'message' => 'Account not Activated!'
                ];
                return $result;
            }
        }
        $result = [
            'found' => false,
            'message' => 'Incorrect Email!'
        ];
        return $result;
    }

    public function countAllUser(): int
    {
        $result = 0;

        $query = $this->queryFactory->newSelect('usertbl')
            ->select('id');
        $rows = $query->execute()->fetchall('assoc');
        $result = count($rows);
        return $result;
    }

    public function getUserTrends(): array
    {

        $result = [];
        $dt = date('Y-m-d');
        $firstDayOfYear = mktime(0, 0, 0, 1, 1, date("2022"));
       $start_date =  date("Y-m-d", $firstDayOfYear);;
       $end_date   =  date("Y-m-t", strtotime($dt)); 
        
        $query = $this->queryFactory->newSelect('usertbl');
        $query->select(['total_users' => $query->func()->count('id'), 'atMonth'=>'MONTH(created)' ,'atYear'=>'Year(created)' ]);
        $query->andWhere([$query->newExpr()->between('created', $start_date, $end_date),]);
        $query->group(['atYear','atMonth']);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result['Months'][] =$row['atMonth'].'-'.$row['atYear'];
            $result['users'][] =$row['total_user'];
        }
        return $result;
    }


    public function countSubscribed(): int
    {
        $result = 0;

        $query = $this->queryFactory->newSelect('usertbl')
            ->select('id')->where(['subscription' => 1]);
        $rows = $query->execute()->fetchall('assoc');
        $result = count($rows);
        return $result;
    }



    public function resetPasswordByMail(array $data): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('usertbl')->select('first_name')->Where(['email' => $data['email']]);
        $rows = $query->execute()->fetch('assoc');
        $first_name = $rows['first_name'];
        if (count($rows) > 0) {
            $tempPass = bin2hex(random_bytes(5));
            $values = [
                'password' =>  password_hash($tempPass, PASSWORD_DEFAULT),
            ];
            $this->queryFactory->newUpdate('usertbl')
                ->set($values)
                ->andWhere(['email' => $data['email']])
                ->execute();
            $result['error'] = false;
            $result['newPass'] = $tempPass;
            $result['description'] = 'Your password has been sent to email. ';
            $result['first_name'] = $first_name;
        } else {
            $result['error'] = true;
            $result['description'] = 'Email account not found';
        }


        return $result;
    }


    public function updatePasswordById(array $data): array
    {
        $values = [
            'password' =>  password_hash($data['password'], PASSWORD_DEFAULT),
        ];
        $this->queryFactory->newUpdate('usertbl')
            ->set($values)
            ->andWhere(['id' => $data['userid']])
            ->execute();
        $result = ['description' => 'Password Change Successful!' . $data['password'] . $data['userid']];
        return $result;
    }

    public function updateUserProfile(array $data): array
    {
        $this_data = [
            'user_id' => $data['userid'],
            'prefix' => isset($data['prefix']) ? $data['prefix'] : null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => isset($data['middle_name']) ? $data['middle_name'] : null,
            'suffix' => isset($data['suffix']) ? $data['suffix'] : null,
            'organization' => $data['organization'],
            'photo' => isset($data['photo']) ? $data['photo'] : null,
            'workphone' => isset($data['workphone']) ? $data['workphone'] : null,
            'cellphone' => $data['cellphone'],
            'title' => isset($data['title']) ? $data['title'] : null,
            'url' => isset($data['url']) ? $data['url'] : null,
            'note' => isset($data['note']) ? $data['note'] : null,
            'logo' => isset($data['logo']) ? $data['logo'] : null,
            'email' => $data['email'],
            'workemail' => isset($data['workemail']) ? $data['workemail'] : null,
            'role' => $data['role'],
            'street' => $data['street'],
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'facebook' => isset($data['facebook']) ? $data['facebook'] : null,
            'linkedin' => isset($data['linkedin']) ? $data['linkedin'] : null,
            'twitter' => isset($data['twitter']) ? $data['twitter'] : null,
            'instagram' => isset($data['instagram']) ? $data['instagram'] : null,
            'usertype' => $data['usertype'],
            'subscription' => $data['subscription'],
            'status' => $data['status'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ];
        $this->queryFactory->newUpdate('profile')
            ->set($this_data)
            ->andWhere(['id' => $data['userid']])
            ->execute();
        $result = ['description' => 'Profile Updated Successfully'];
        return $result;
    }

    public function updateProfilePicture(array $data): array
    {
        $this_data = [

            'profilepicture' => $data['photo']
        ];
        $this->queryFactory->newUpdate('usertbl')
            ->set($this_data)
            ->andWhere(['user_id' => $data['user_id']])
            ->execute();
        $result = ['description' => 'Profile Updated Successfully'];
        return $result;
    }

    public function enableUser(int $user_id): void
    {
        $values = [
            'status' => 1,
        ];
        $this->queryFactory->newUpdate('profile')
            ->set($values)
            ->andWhere(['id' => $user_id])
            ->execute();
    }

    public function enableUserByUserID(string $userid): array
    {

        $query = $this->queryFactory->newSelect('usertbl')->select(['user_id', 'status'])->Where(['user_id' => $userid]);
        $row = $query->execute()->fetch('assoc');
        $result = [];
        if ($row) {
            if ((int) $row['status'] > 0) {
                $result = ['code' => 200, 'message' => 'Your account has already been verified!'];
            } else {
                $values = [
                    'status' => 1,
                ];
                $this->queryFactory->newUpdate('usertbl')
                    ->set($values)
                    ->andWhere(['user_id' => $userid])
                    ->execute();
                $result = ['code' => 200, 'message' => 'Your account has been verified successfully!'];
            }
        } else {
            $result = ['code' => 500, 'message' => 'Invalid account verification Code!'];
        }
        return $result;
    }


    public function disableUser(int $user_id): void
    {
        $values = [
            'status' => 0,
        ];
        $this->queryFactory->newUpdate('profile')
            ->set($values)
            ->andWhere(['id' => $user_id])
            ->execute();
    }




    public function getUserById(int $id): array
    {
        $query = $this->queryFactory->newSelect('profile')
            ->select('*')
            ->Where(['profile.id' => $id, 'status' => 1]);
        $row = $query->execute()->fetch('assoc');

        return $row;
    }

    public function getUserByUserId(string $user_id): array
    {
        $query = $this->queryFactory->newSelect('usertbl')
            ->select('*')
            ->Where(['user_id' => $user_id]);
        $row = $query->execute()->fetch('assoc');
        return $row;
    }

    public function getUserPicByUserId(string $user_id): array
    {
        $query = $this->queryFactory->newSelect('usertbl')
            ->select('profilepicture')
            ->Where(['user_id' => $user_id]);
        $row = $query->execute()->fetch('assoc');
        return $row;
    }

    public function getActiveUsers(): array
    {
        $query = $this->queryFactory->newSelect('profile')
            ->select('*')
            ->Where(['status' => 1]);
        $rows = $query->execute()->fetchAll('assoc');

        $result = [];
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getInActiveUsers(): array
    {
        $query = $this->queryFactory->newSelect('profile')
            ->select([
                'profile.id', 'profile.first_name', 'profile.last_name', 'profile.email', 'profile.dob', 'profile.cellphone',
                'profile.address', 'city' => 'cities.name', 'state' => 'states.name', 'profile.zipcode', 'profile.password', 'profile.tin', 'profile.date_of_inc',
                'profile.user_type', 'country' => 'countries.name', 'profile.company_name', 'location' => 'profile.seller_loc', 'profile.level', 'profile.status'
            ])
            ->leftJoin('cities', 'profile.city = cities.id')
            ->leftJoin('states', 'profile.state = states.id')
            ->leftJoin('countries', 'profile.country = countries.id');
        // ->Where(['status'=>0]);
        $rows = $query->execute()->fetchAll('assoc');

        $result = [];
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }
}
