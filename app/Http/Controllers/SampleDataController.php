<?php

namespace App\Http\Controllers;

use Mail;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Goal;
use App\Models\Objective;
use App\Models\Vision;
use App\Models\Issue;
use App\Models\Question;
use App\Models\Billing;
use App\Models\AccountabilityCall;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Str;

class SampleDataController extends Controller
{
    /**
     * Sample data calculation and formatting
     *
     * @return \Illuminate\Support\Collection
     */
    public function profits()
    {
        $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        $data = collect(json_decode(file_get_contents(resource_path('samples/sales.json'))));

        $d = $data->groupBy(function ($data) {
            return Carbon::parse($data->datetime)->format('Y-m');
        })->map(function ($data) {
            return [
                'profit'  => number_format($data->sum('profit') / 11, 2),
                'revenue' => number_format($data->sum('revenue') / 13, 2),
            ];
        })->sortKeys()->mapWithKeys(function ($data, $key) use ($months) {
            return [$months[Carbon::parse($key)->format('n')] => $data];
        });

        return $d;
    }

    public function getUsers(Request $request)
    {
        $usersDetails = [];
        if ($request->id) { // Get Team users by login id
            $idsByTeam = Task::where('user_id', '=', $request->id)
                        ->get('coach_ids');

            if ($request->search) { // Search by first & last name
                $usersDetail = User::join('user_infos', 'users.id', '=', 'user_infos.user_id')
                    ->select([
                    'users.*',
                    DB::raw("CONCAT(users.first_name,' ',users.last_name) as name"),
                    DB::raw("substr(users.first_name, 0, 1) as label"),
                    "users.updated_at as last_login",
                    "users.created_at as joined_day",
                    'user_infos.*'])
                    ->where('first_name', 'LIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'LIKE', "%{$request->search}%")
                    ->whereIn('users.id', json_decode($idsByTeam[0]->coach_ids) )
                    ->take(5)
                    ->get();
    
                return response()->json([
                    'data'=> $usersDetail
                ]);
            }
    
            if ($idsByTeam[0]) { // if login id is in team users & in tasks
                $usersDetails = User::join('user_infos', 'users.id', '=', 'user_infos.user_id')
                    ->select([
                        DB::raw("users.id as id"),
                        DB::raw("CONCAT(users.first_name,' ',users.last_name) as name"),
                        DB::raw("substr(users.first_name, 0, 1) as label"),
                        "users.updated_at as last_login",
                        "users.created_at as joined_day",
                        'user_infos.*',
                        'users.*',
                    ])
                    ->whereIn('users.id', json_decode($idsByTeam[0]->coach_ids) )
                    ->get();
            }
        } else {
            if ($request->search) { // Search by first & last name
                $usersDetail = User::join('user_infos', 'users.id', '=', 'user_infos.user_id')
                    ->select([
                    'users.*',
                    DB::raw("CONCAT(users.first_name,' ',users.last_name) as name"),
                    DB::raw("substr(users.first_name, 0, 1) as label"),
                    "users.updated_at as last_login",
                    "users.created_at as joined_day",
                    'user_infos.*'])
                    ->where('first_name', 'LIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'LIKE', "%{$request->search}%")
                    ->take(5)
                    ->get();
    
                return response()->json([
                    'data'=> $usersDetail
                ]);
            }
    
            $usersDetails = User::join('user_infos', 'users.id', '=', 'user_infos.user_id')
                ->select([
                    DB::raw("users.id as id"),
                    DB::raw("CONCAT(users.first_name,' ',users.last_name) as name"),
                    DB::raw("substr(users.first_name, 0, 1) as label"),
                    "users.updated_at as last_login",
                    "users.created_at as joined_day",
                    'user_infos.*',
                    'users.*',
                ])
                ->get();
        }

        return response()->json([
            'data'=> $usersDetails
        ]);
    }

    public function getUserDetail(Request $request)
    {
        $usersDetail = User::join('user_infos', 'users.id', '=', 'user_infos.user_id')
            ->select([
              'users.*',
              DB::raw("CONCAT(users.first_name,' ',users.last_name) as name"),
              DB::raw("substr(users.first_name, 0, 1) as label"),
              "users.updated_at as last_login",
              "users.created_at as joined_day",
              'user_infos.*'
            ])->where('user_id', $request->id)->get();

        return response()->json([
            'data'=> $usersDetail[0]
        ]);
    }

    public function deleteUserDetail(Request $request)
    {
        UserInfo::where('user_id', $request->id)->delete();
        User::where('id', $request->id)->delete();
        return response()->json([
            'message'=>'User Deleted Successfully!'
        ]);
    }

    public function apiUser(Request $request)
    {
        $userInfo = UserInfo::where('user_id', $request->id)->first();
        return response()->json($userInfo);
    }

    public function apiCreateUser(Request $request)
    {
        $splitName = explode(' ', $request->name, 2); // Restricts it to only 2 values, for names like Billy Bob Jones
        $first_name = $splitName[0];
        $last_name = !empty($splitName[1]) ? $splitName[1] : ''; // If last name doesn't exist, make it empty

        $token = Str::random(60);
        $remember_token = Str::random(60);
        $user = User::create([
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $request->email,
            'email_verified_at' => now(),
            'password'   => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            //'password'   => Hash::make($request->password),
            'api_token' => hash('sha256', $token),
            'remember_token'    => hash('sha256', $remember_token),
        ]);

        $userInfo = [
            //'avatar' => $request->avatar,
            'role' => $request->role,
        ];

        $info = new UserInfo();
        foreach ($userInfo as $key => $value) {
            $info->$key = $value;
        }
        $info->user()->associate($user);
        $info->save();
        
        return response()->json([
            'message'=>'User Created Successfully!'
        ]);
    }

    public function apiUpdateUser(Request $request)
    {
        // save user name
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
        ]);

        User::where('id', $request->id)->update($validated);
        $updateUserInfo = array(
            'avatar'=> $request->avatar, 
            'company'=> $request->company,
            'phone'=> $request->phone, 
            'website'=> $request->website,
            'country'=> $request->country, 
            'language'=> $request->language,
            'timezone'=> $request->timeZone,
            'currency'=> $request->currency,
            'communication'=> serialize($request->communication),
            'marketing'=> $request->marketing
        );
        // save on user info
        UserInfo::where('user_id', $request->id)->update($updateUserInfo);

        // // attach this info to the current user
        // $info->user()->associate(auth()->user());

        // foreach ($request->only(array_keys($request->rules())) as $key => $value) {
        //     if (is_array($value)) {
        //         $value = serialize($value);
        //     }
        //     $info->$key = $value;
        // }

        // // include to save avatar
        // if ($avatar = $this->upload()) {
        //     $info->avatar = $avatar;
        // }

        // if ($request->boolean('avatar_remove')) {
        //     Storage::delete($info->avatar);
        //     $info->avatar = null;
        // }

        // $info->save();

        return response()->json([
            'message'=>'User Updated Successfully!'
        ]);
    }

    public function apiUpdateEmail(Request $request)
    {
        User::where('id', $request->id)->update(
            [
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]
        );

        return response()->json([
            'message'=>'Email Updated Successfully!'
        ]);
    }

    public function apiUpdatePassword(Request $request)
    {
        User::where('id', $request->id)->update(
            [
                'password' => Hash::make($request->newPassword)
            ]
        );

        return response()->json([
            'message'=>'Password Updated Successfully!'
        ]);
    }

    public function apiEmailPreferences(Request $request)
    {
        UserInfo::where('id', $request->id)->update(
            [
                'email_preference' => $request->emailPreference
            ]
        );

        return response()->json([
            'message'=>'Email Preferences Updated Successfully!'
        ]);
    }

    public function apiDeactivateProfile(Request $request)
    {
        User::where('id', $request->id)->delete();
        UserInfo::where('user_id', $request->id)->delete();

        return response()->json([
            'message'=>'Account has been successfully deleted!'
        ]);
    }

    public function apiBilling(Request $request)
    {
        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/apikeys
        if ($request->choice == "2") $amount = 710000;
        else $amount = 295000;

        \Stripe\Stripe::setApiKey('sk_test_');

        $customer = \Stripe\Customer::create([
            'email' => $request->email,
            'name' => $request->cardName
        ]);

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'gbp',
            'customer' => $customer['id'],
            'receipt_email' => 'test@gmail.com',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            // 'payment_method_types' => [
            //     'bancontact',
            //     'card',
            //     'eps',
            //     'giropay',
            //     'ideal',
            //     'p24',
            //     'sepa_debit',
            //     'sofort',
            // ]
        ]);
        //$client_secret = $intent->client_secret;
        // Pass the client secret to the client

        Billing::create([
            'user_id'      => $request->user_id,
            'cardName'      => $request->cardName,
            'choice'      => $request->choice,
            'customer_id' => $customer['id'],
            //'cardNumber'      => $paymentIntent['source']['card']['last4'],
            //'expirationDate'      => $paymentIntent['source']['card']['exp_month'] . "/" . $paymentIntent['source']['card']['exp_year'],
            // 'cvv'      => $request->cvv,
        ]);

        return response($paymentIntent);
    }

    public function getBillingByUser(Request $request)
    {
        $billingDetail = Billing::where('user_id', $request->id)->get();

        return response()->json([
            'data'=> $billingDetail
        ]);
    }

    public function updateBusinessDetail(Request $request)
    {
        $updateUserInfo = array(
            'business_detail'=> $request->business_detail,
            'business_dream'=> $request->business_dream,
            'point_distraction'=> $request->point_distraction,
            'point_productive'=> $request->point_productive,
            'task_first'=> $request->task_first,
            'task_second'=> $request->task_second,
            'task_third'=> $request->task_third,
        );
        // save on user info
        UserInfo::where('user_id', $request->user_id)->update($updateUserInfo);

        return response()->json([
            'message'=>'User Updated Business detail Successfully!'
        ]);
    }

    public function getTasks(Request $request)
    {
        if ($request->search) {
            $tasksDetails = Task::where('name', 'LIKE', "%$request->search%")
                ->take(5)->get();
        } else {
            $tasksDetails = Task::orderBy('name', 'ASC')->get();
        }

        return response()->json([
            'data'=> $tasksDetails
        ]);
    }

    public function createTask(Request $request)
    {
        $request->validate([
            'taskName' => 'required|string|max:255',
        ]);

        $uncommitted = "1";
        Task::create([
            'name'          => $request->taskName,
            'overview'      => $request->taskOverview,
            'milestone_id'  => $request->milestoneId,
            'objective_id'  => $request->objectiveId,
            'priority'      => $request->priority,
            'uncommitted'   => $uncommitted,
            'user_id'       => $request->user_id    
        ]);

        return response()->json([
            'message'=>'Task Created Successfully!'
        ]);
    }

    public function getTaskDetail(Request $request)
    {
        $taskDetail = Task::find($request->id);

        return response()->json([
            'data'=> $taskDetail
        ]);
    }

    public function updateTask(Request $request)
    {
        if ($request->updateParam) {
            if ($request->updateParam == "completed") {
                $updateArray = [$request->updateParam => '1', 'committed' => '0', 'uncommitted' => '0', 'rejected' => '0', 'process' => '0', 'archived' => '0'];
            } elseif ($request->updateParam == "archived") {
                $updateArray = [$request->updateParam => '1', 'committed' => '0', 'uncommitted' => '0', 'rejected' => '0', 'process' => '0', 'completed' => '0'];
            } elseif ($request->updateParam == "process") {
                $updateArray = [$request->updateParam => '1', 'committed' => '0', 'uncommitted' => '0', 'rejected' => '0', 'completed' => '0', 'archived' => '0'];
            } elseif ($request->updateParam == "rejected") {
                $updateArray = [$request->updateParam => '1', 'committed' => '0', 'uncommitted' => '0', 'completed' => '0', 'process' => '0', 'archived' => '0'];
            } elseif ($request->updateParam == "committed") {
                $updateArray = [$request->updateParam => '1', 'uncommitted' => '0', 'completed' => '0', 'rejected' => '0', 'process' => '0', 'archived' => '0', 
                    'created_at' => Carbon::now()];
            } elseif ($request->updateParam == "uncommitted") {
                $updateArray = [$request->updateParam => '1', 'committed' => '0', 'completed' => '0', 'rejected' => '0', 'process' => '0', 'archived' => '0'];
            } elseif ($request->updateParam == "delete") {
                Task::find($request->updateId)->delete();
                return response()->json([
                    'message'=>'Task Deleted Successfully!'
                ]);
            }
            Task::find($request->updateId)->update($updateArray);
        } else {
            //$request->taskDetail['created_at'] = Carbon::parse(trim($request->taskDetail['created_at']))->format('Y-m-d g:i:s A');
            Task::find($request->taskDetail['id'])->update($request->taskDetail);
        }
        return response()->json([
            'message'=>'Task Updated Successfully!'
        ]);
    }

    public function updateUncommittedTask(Request $request)
    {
        $date = Carbon::parse(trim($request->taskDate))->format('Y-m-d g:i:s A');
        $updateArray = [
            'committed' => '1',
            'uncommitted' => '0',
            'completed' => '0',
            'created_at' => $date,
            'length' => $request->taskLength,
        ];
        Task::find($request->uncommittedTask)->update($updateArray);
        return response()->json([
            'message'=>'You have now successfully committed your task.'
        ]);
    }

    public function updateTaskByKanban(Request $request)
    {
        if ($request->destination == "5" && $request->source != "5") {
            $updateArray = [
                'committed' => '0',
                'uncommitted' => '0',
                'completed' => '1',
                // 'created_at' => $request->date,
            ];
        } else if ($request->source == "5" && $request->destination != "5") {
            $updateArray = [
                'committed' => '1',
                'uncommitted' => '0',
                'completed' => '0',
                'created_at' => $request->date,
            ];
        } else {
            $updateArray = [
                'created_at' => $request->date,
            ];
        }
        Task::find($request->id)->update($updateArray);
        return response()->json([
            'message'=>'You have now successfully updated your task.'
        ]);
    }

    public function updateTaskByCalendar(Request $request)
    {
        $updateArray = [
            'created_at' => $request->created_at,
        ];
        Task::find($request->id)->update($updateArray);
        return response()->json([
            'message'=>'You have now successfully updated your task.'
        ]);
    }

    public function taskDayInfo(Request $request)
    {
        $date = Carbon::createFromFormat('Y-m-d', $request->date);
        $idsByTeam = Task::whereYear('created_at', '=', $date->year)
                        ->whereMonth('created_at', '=', $date->month)
                        ->whereDay('created_at', '=', $date->day)
                        ->where('committed', '=', "1")
                        ->where('user_id', '=', $request->user_id)
                        ->get('coach_ids');
        $tasksDetails = [];
        if (isset($idsByTeam[0])) {
            $tasksDetails = Task::whereYear('created_at', '=', $date->year)
                        ->whereMonth('created_at', '=', $date->month)
                        ->whereDay('created_at', '=', $date->day)
                        ->where('committed', '=', "1")
                        ->where('user_id', '=', $request->user_id)
                        ->whereIn('user_id', json_decode($idsByTeam[0]->coach_ids))
                        ->get();
        }

        return response()->json([
            'data'=> $tasksDetails
        ]);
    }

    public function getMilestones()
    {
        $milestonesDetails = Milestone::orderBy('due_date', 'ASC')
            ->orderBy( 'name', 'ASC')
            ->get();

        return response()->json([
            'data'=> $milestonesDetails
        ]);
    }

    public function createMilestone(Request $request)
    {
        $request->validate([
            'milestoneName' => 'required|string|max:255',
        ]);

        $milestoneDetail = Milestone::create([
            'name'              => $request->milestoneName,
            'overview'          => $request->milestoneOverview,
            'relation_object'   => $request->relationObject,
            'due_date'          => $request->dueDate,
        ]);

        if ($request->relationObject == "1" && isset($milestoneDetail['id'])) {
            Objective::create([
                'title'         => $request->milestoneName,
                'status'        => '1',
                'parent'        => '0',
                'milestone_id'  => $milestoneDetail['id']
            ]);
        }

        return response()->json([
            'message' => 'Milestone Created Successfully!'
        ]);
    }

    public function getMilestoneDetail(Request $request)
    {
        $milestoneDetail = Milestone::find($request->id);

        return response()->json([
            'data'=> $milestoneDetail
        ]);
    }

    public function updateMilestone(Request $request)
    {
        if ($request->milestoneDetail['status'] == '1') {
            Milestone::find($request->milestoneDetail['id'])->delete();
            Goal::create([
                'name'          => $request->milestoneDetail['name'],
                'overview'      => $request->milestoneDetail['overview'],
                'type'          => 'business',
                'completed'     => '1'
            ]);
            $updateArray = ['status' => '5'];
            Objective::where('milestone_id', '=', $request->milestoneDetail['id'])->update($updateArray);
        } else {
            Milestone::find($request->milestoneDetail['id'])->update($request->milestoneDetail);
        }
        return response()->json([
            'message'=>'Milestone Updated Successfully!'
        ]);
    }

    public function deleteMilestone(Request $request)
    {
        if ($request->updateParam == "delete") {
            Milestone::find($request->updateId)->delete();
            return response()->json([
                'message'=>'Milestone Deleted Successfully!'
            ]);
        } else if ($request->updateParam == "complete") {
            //Milestone::find($request->updateId)->update(['status' => '1']);
            $info = Milestone::find($request->updateId);
            Milestone::find($request->updateId)->delete();
            Goal::create([
                'name'          => $info['name'],
                'overview'      => $info['overview'],
                'type'          => 'business',
                'completed'     => '1'
            ]);
            return response()->json([
                'message'=>'Milestone Completed Successfully!'
            ]);
        }
    }

    public function getGoals()
    {
        $goalsDetails = Goal::orderBy('priority', 'DESC', 'id', 'DESC')->get();

        return response()->json([
            'data'=> $goalsDetails
        ]);
    }

    public function createGoal(Request $request)
    {
        $request->validate([
            'goalName' => 'required|string|max:255',
        ]);

        if ($request->priority == "1") {
            Goal::where('type', $request->type)->update(['priority' => '0']);   
        } else {
            Goal::where('type', $request->type)
                ->where('priority', '<>', '1')
                ->update(['priority' => '0']);   
        }

        Goal::create([
            'name'          => $request->goalName,
            'overview'      => $request->goalOverview,
            'type'          => $request->type,
            'department'    => $request->department,
            'priority'      => $request->priority,
            //'update_at'     => Carbon::parse($request->value)->format('Y-m-d H:M:S'),
        ]);

        return response()->json([
            'message'=>'Goal Created Successfully!'
        ]);
    }

    public function updateGoal(Request $request)
    {
        if ($request->updateParam) {
            if ($request->updateParam == "priority") {
                Goal::where('type', $request->type)->update(['priority' => '0']);
                Goal::find($request->id)->update(['priority' => '1']);
            } else if ($request->updateParam == "removePriority") {
                Goal::where('type', $request->type)->update(['priority' => '0']);
            } else if ($request->updateParam == "activeGoal") {
                Goal::find($request->updateId)->update(['completed' => '0']);
            }
        } else {
            if ($request->goalDetail['priority'] == "1") {
                Goal::where('type', $request->goalDetail['type'])->update(['priority' => '0']);
            }
            Goal::find($request->goalDetail['id'])->update($request->goalDetail);
        }
        return response()->json([
            'message'=>'Goal Updated Successfully!'
        ]);
    }
    
    public function getGoalDetail(Request $request)
    {
        $goalDetail = Goal::find($request->id);

        return response()->json([
            'data'=> $goalDetail
        ]);
    }

    public function deleteGoal(Request $request)
    {
        Goal::where('id', $request->id)->delete();
        return response()->json([
            'message'=>'Goal Deleted Successfully!'
        ]);
    }

    public function getVisions()
    {
        $visionsDetails = Vision::get();

        return response()->json([
            'data'=> $visionsDetails
        ]);
    }

    public function createVision(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'overview'  => 'required|string',
        ]);

        $visionDetail = Vision::create([
            'title'         => $request->title,
            'overview'      => $request->overview,
        ]);

        return response()->json([
            'message'=> $visionDetail['title'] . ' created successfully!'
        ]);
    }

    public function getVisionDetail(Request $request)
    {
        $visionDetail = Vision::find($request->id);

        return response()->json([
            'data'=> $visionDetail
        ]);
    }

    public function visionDetailByParam(Request $request)
    {
        $visionDetail = Vision::where('title', '=', $request->type)->get();

        return response()->json([
            'data'=> $visionDetail
        ]);
    }

    public function updateVision(Request $request)
    {
        $visionDetail = Vision::find($request->visionDetail['id']);
        Vision::find($request->visionDetail['id'])->update($request->visionDetail);
        if ($visionDetail['title'] == "Business" || $visionDetail['title'] == "Personal") {
            $visionDetail['title'] = "Your Why";
        }
        return response()->json([
            'message'=> $visionDetail['title'] . ' Updated Successfully!'
        ]);
    }

    public function deleteVision(Request $request)
    {
        $visionDetail = Vision::find($request->id);
        if ($visionDetail['title'] == "Business" || $visionDetail['title'] == "Personal") {
            $visionDetail['title'] = "Your Why";
        }
        Vision::where('id', $request->id)->delete();
        return response()->json([
            'message'=> $visionDetail['title'] . ' Deleted Successfully!'
        ]);
    }

    public function getIssues()
    {
        $issuesDetails = Issue::get();

        return response()->json([
            'data'=> $issuesDetails
        ]);
    }

    public function createIssue(Request $request)
    {
        Issue::create($request->issueDetail);

        return response()->json([
            'message'=>'Issue Created Successfully!'
        ]);
    }

    public function getIssueDetail(Request $request)
    {
        $issueDetail = Issue::find($request->id);

        return response()->json([
            'data'=> $issueDetail
        ]);
    }

    public function updateIssue(Request $request)
    {
        Issue::find($request->issueDetail['id'])->update($request->issueDetail);
        return response()->json([
            'message'=>'Issue Updated Successfully!'
        ]);
    }

    public function deleteIssue(Request $request)
    {
        Issue::where('id', $request->id)->delete();
        return response()->json([
            'message'=>'Issue Deleted Successfully!'
        ]);
    }

    public function getQuestions(Request $request)
    {
        $questionsDetails = Question::whereDate('created_at', '>=', $request->startWeek)
            ->whereDate('created_at', '<=', $request->endWeek)
            ->get();

        return response()->json([
            'data'=> $questionsDetails
        ]);
    }

    public function createQuestion(Request $request)
    {
        $request->validate([
            'overview'  => 'required|string',
        ]);

        Question::create([
            'question_id'   => $request->questionId,
            'overview'      => $request->overview,
        ]);

        return response()->json([
            'message'=>'Question Created Successfully!'
        ]);
    }
    
    public function getQuestionDetail(Request $request)
    {
        $questionDetail = Question::find($request->id);

        return response()->json([
            'data'=> $questionDetail
        ]);
    }

    public function updateQuestion(Request $request)
    {
        Question::find($request->questionDetail['id'])->update($request->questionDetail);
        return response()->json([
            'message'=>'Question Updated Successfully!'
        ]);
    }

    public function deleteQuestion(Request $request)
    {
        Question::where('id', $request->id)->delete();
        return response()->json([
            'message'=>'Question Deleted Successfully!'
        ]);
    }

    public function weekyReview(Request $request)
    {
        $date = Carbon::createFromFormat('Y-m-d', $request->weeklyLogDate);
        $weeklyId = Question::whereYear('created_at', '=', $date->year)
            ->whereMonth('created_at', '=', $date->month)
            ->whereDay('created_at', '=', $date->day)
            ->pluck('id')->toArray();
        if (isset($weeklyId) && $weeklyId != []) {
            Question::whereIn('id', $weeklyId)->update(['overview' => $request->emoji]);
            return response()->json([
                'message'=>'Weekly review Updated Successfully!'
            ]);
        } else {
            Question::create([
                'question_id'   => 6,
                'overview'      => $request->emoji,
                'created_at'    => $request->weeklyLogDate
            ]);
            return response()->json([
                'message'=>'Weekly review Created Successfully!'
            ]);
        }
    }

    public function getObjectives(Request $request)
    {
        if ($request->search) {
            $objectivesDetails = Objective::where('title', 'LIKE', "%$request->search%")
                ->where('parent', '=', '0' )
                ->take(5)->get();
        } else if ($request->parent == '0') {
            $objectivesDetails = Objective::where('parent', '=', $request->parent)->get();
        } else {
            $objectivesDetails = Objective::get();
        }

        return response()->json([
            'data'=> $objectivesDetails
        ]);
    }

    public function getSubObjectives(Request $request)
    {
        $objectivesDetails = Objective::where('parent', '=', $request->id)->get();

        return response()->json([
            'data'=> $objectivesDetails
        ]);
    }

    public function searchObjectives(Request $request)
    {
        if ($request->cycle) {
            $updateArray = ['cycle' => $request->cycle, 'parent' => $request->id];
            $objectivesDetails = Objective::where($updateArray)->get();
        } else if ($request->updateParam) {
            $clauses = [];
            if(isset($request->updateParam['department']) && $request->updateParam['department'] != "0") {
                $objectivesParents = Objective::where('department', '=', $request->updateParam['department'])->pluck('id')->toArray();
                //$objectivesDetails[0] = ['id' => '', 'title' => '', 'assign_to' => '', 'status' => '', 'key_result_title' => '', 'assign_owner' => '', 'target' => '', 'cycle' => '', 'parent' => '', 'department' => ''];
            }
            if (!isset($request->updateParam['department'])) {
                $objectivesParents = Objective::whereIn('department', [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13])->pluck('id')->toArray();
            }
            if(isset($request->updateParam['status']) && $request->updateParam['status'] != "0") {
                $clauses = array_merge($clauses,['status' => $request->updateParam['status']]);
            }
            if(isset($request->updateParam['assign_to'])) {
                $clauses = array_merge($clauses,['assign_to' => $request->updateParam['assign_to']]);
            }
            $key_result_title = $request->updateParam['key_result_title'];
            $objectivesDetails = Objective::where('key_result_title', 'LIKE', "%$key_result_title%")
                ->whereIn('parent', $objectivesParents)
                ->where($clauses)
                ->get();
        }

        return response()->json([
            'data'=> $objectivesDetails
        ]);
    }

    public function searchUnflattenObjectives(Request $request)
    {
        if ($request->cycle || $request->cycle == '0') {
            if(isset($request->cycle) && $request->cycle != '0') {
                $idsByCycle = Objective::where('cycle', $request->cycle)
                    ->where('parent', '=', "0")
                    ->pluck('id')->toArray();
                $idsByCycle = Objective::whereIn('id', $idsByCycle)
                    ->orWhereIn('parent', $idsByCycle)
                    ->pluck('id')->toArray();
            } else if (!isset($request->cycle) || $request->cycle == '0') {
                $idsByCycle = Objective::where('parent', '=', "0")->pluck('id')->toArray();
                $idsByCycle = Objective::whereIn('id', $idsByCycle)
                    ->orWhereIn('parent', $idsByCycle)
                    ->pluck('id')->toArray();
            }
            $objectivesDetails = Objective::whereIn('id', $idsByCycle)->get();

        } else if ($request->queryWeek) {
            $from = date($request->queryWeek['first']);
            $to = date($request->queryWeek['last']);
            $idsByWeek = Objective::whereBetween('created_at', [$from, $to])
                ->orWhere('parent', '=', "0")
                ->pluck('id')->toArray();
            $objectivesDetails = Objective::whereIn('id', $idsByWeek)->get();

        } else if ($request->updateParam) {
            // Search by Title
            if (isset($request->updateParam['title'])) {
                $title = $request->updateParam['title'];
                $idsByTitle = Objective::where('title', 'LIKE', "%$title%")
                    ->where('parent', '=', "0")
                    ->pluck('id')->toArray();
                $idsByTitle = Objective::whereIn('id', $idsByTitle)
                    ->orWhereIn('parent', $idsByTitle)
                    ->pluck('id')->toArray();
            } else if (!isset($request->updateParam['title'])) {
                $idsByTitle = Objective::where('parent', '=', "0")->pluck('id')->toArray();
                $idsByTitle = Objective::whereIn('id', $idsByTitle)
                    ->orWhereIn('parent', $idsByTitle)
                    ->pluck('id')->toArray();
            }
            // Serach by key_result_title
            if (isset($request->updateParam['key_result_title'])) {
                $keyResultTitle = $request->updateParam['key_result_title'];
                $idsBykeyResultTitle = Objective::where('key_result_title', 'LIKE', "%$keyResultTitle%")
                    ->where('parent', '!=', "0")
                    ->pluck('id')->toArray();
                $tempParent = Objective::where('key_result_title', 'LIKE', "%$keyResultTitle%")
                    ->where('parent', '!=', "0")
                    ->pluck('parent')->toArray();
                $idsBykeyResultTitle = Objective::whereIn('id', $idsBykeyResultTitle)
                    ->orWhereIn('id', $tempParent)
                    ->pluck('id')->toArray();
            } else if (!isset($request->updateParam['key_result_title'])) {
                $idsBykeyResultTitle = Objective::where('parent', '=', "0")->pluck('id')->toArray();
                $idsBykeyResultTitle = Objective::whereIn('id', $idsBykeyResultTitle)
                    ->orWhereIn('parent', $idsBykeyResultTitle)
                    ->pluck('id')->toArray();
            }
            // Search by Status
            if (isset($request->updateParam['status']) && $request->updateParam['status'] != "0") {
                $idsByStatus = Objective::where('status', '=', $request->updateParam['status'])
                    ->where('parent', '=', "0")
                    ->pluck('id')->toArray();
                $idsByStatus = Objective::whereIn('id', $idsByStatus)
                    ->orWhereIn('parent', $idsByStatus)
                    ->pluck('id')->toArray();
            } else if (!isset($request->updateParam['status']) || $request->updateParam['status'] == "0") {
                $idsByStatus = Objective::whereIn('status', [0, 1, 2, 3, 4, 5])->pluck('id')->toArray();
                $idsByStatus = Objective::whereIn('id', $idsByStatus)
                    ->orWhereIn('parent', $idsByStatus)
                    ->pluck('id')->toArray();
            }
            // Search by assign_to_parent
            if (isset($request->updateParam['assign_to_parent'])) {
                $idsByAssign = Objective::where('assign_to_parent', $request->updateParam['assign_to_parent'])->pluck('id')->toArray();
                $idsByAssign = Objective::whereIn('id', $idsByAssign)
                    ->orWhereIn('parent', $idsByAssign)
                    ->pluck('id')->toArray();
            } else if (!isset($request->updateParam['assign_to_parent'])) {
                $idsByAssign = Objective::pluck('id')->toArray();
                $idsByAssign = Objective::whereIn('id', $idsByAssign)
                    ->orWhereIn('parent', $idsByAssign)
                    ->pluck('id')->toArray();
            }
            // Search by Department
            if (isset($request->updateParam['department']) && $request->updateParam['department'] != "0") {
                $idsByDepartment = Objective::where('department', $request->updateParam['department'])
                    ->where('parent', '=', "0")
                    ->pluck('id')->toArray();
                $idsByDepartment = Objective::whereIn('id', $idsByDepartment)
                    ->orWhereIn('parent', $idsByTitle)
                    ->pluck('id')->toArray();
            } else if (!isset($request->updateParam['department']) || $request->updateParam['department'] == "0") {
                $idsByDepartment = Objective::whereIn('department', [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13])->pluck('id')->toArray();
                $idsByDepartment = Objective::whereIn('id', $idsByDepartment)
                    ->orWhereIn('parent', $idsByDepartment)
                    ->pluck('id')->toArray();
            }
            // Search by Cycle
            if(isset($request->updateParam['cycle']) && $request->updateParam['cycle'] != '0') {
                $idsByCycle = Objective::where('cycle', $request->updateParam['cycle'])
                    ->where('parent', '=', "0")
                    ->pluck('id')->toArray();
                $idsByCycle = Objective::whereIn('id', $idsByCycle)
                    ->orWhereIn('parent', $idsByCycle)
                    ->pluck('id')->toArray();
            } else if (!isset($request->updateParam['cycle']) || $request->updateParam['cycle'] == '0') {
                $idsByCycle = Objective::where('parent', '=', "0")->pluck('id')->toArray();
                $idsByCycle = Objective::whereIn('id', $idsByCycle)
                    ->orWhereIn('parent', $idsByCycle)
                    ->pluck('id')->toArray();
            }

            $objectivesDetails = Objective::whereIn('id', $idsByTitle)
                ->whereIn('id', $idsByStatus)
                ->whereIn('id', $idsByAssign)
                ->whereIn('id', $idsByDepartment)
                ->whereIn('id', $idsBykeyResultTitle)
                ->whereIn('id', $idsByCycle)
                ->get();
        }

        return response()->json([
            'data'=> $objectivesDetails
        ]);
    }
    
    public function createObjective(Request $request)
    {
        Objective::create($request->objectiveDetail);
        return response()->json([
            'message'=>'Key result Created Successfully!'
        ]);
    }

    public function createParentObjective(Request $request)
    {
        Objective::create([
            'title'                 => $request->objectiveDetail['title'],
            'assign_to_parent'      => $request->objectiveDetail['assign_to_parent'],
            'department'            => $request->objectiveDetail['department'],
            'status'                => $request->objectiveDetail['status'],
            'parent'                => '0',
        ]);

        return response()->json([
            'message'=>'Objective Created Successfully!'
        ]);
    }

    public function getObjectiveParent(Request $request)
    {
        if ($request->id) {
            $objectiveParent = Objective::find($request->id);
        } else {
            $objectiveParent = Objective::where('parent', '=', $request->id)->get();
        }

        return response()->json([
            'data'=> $objectiveParent
        ]);
    }

    public function getObjectiveDetail(Request $request)
    {
        $objectiveDetail = Objective::find($request->id);

        return response()->json([
            'data'=> $objectiveDetail
        ]);
    }

    public function updateObjective(Request $request)
    {
        Objective::find($request->objectiveDetail['id'])->update($request->objectiveDetail);
        if (isset($request->objectiveDetail['milestone_id'])) {
            $updateArray = ['name' => $request->objectiveDetail['title']];
            $checkMilestone = Milestone::find($request->objectiveDetail['milestone_id']);
            if (isset($checkMilestone)) $checkMilestone->update($updateArray);
        }
        return response()->json([
            'message'=>'Objective Updated Successfully!'
        ]);
    }

    public function deleteObjective(Request $request)
    {
        Objective::where('id', $request->id)->delete();
        return response()->json([
            'message'=>'Objective Deleted Successfully!'
        ]);
    }

    public function deleteParentObjective(Request $request)
    {
        Objective::where('id', $request->id)->delete();
        return response()->json([
            'message'=>'Objective Deleted Successfully!'
        ]);
    }

    public function accountabilityCall(Request $request)
    {
        $AccountabilityCall = AccountabilityCall::create($request->accountabilityDetail);
        $user_email = User::find((int)$request->accountabilityDetail['id']);

        Mail::send('pages.index', ['user' => $AccountabilityCall], function ($m) use ($AccountabilityCall, $user_email) {
            $m->from('test@gmail.com', 'test');
 
            $m->to($user_email['email'] , $AccountabilityCall->name)->subject($AccountabilityCall->comments);
        });

        return response()->json([
            'message'=>'Request to change accountability call time sent. Your coach will be in contact shortly. Note if it is within 24 hours we may not be able to accommodate the change for this week as per our terms and conditions'
            //'message' => $user_email['email']
        ]);
    }

    public function apiActivity(Request $request) {
        if ($request->limit) {
            $limit = $request->limit;
        } else {
            $limit = 10;
        }
        $activityDetails = Activity::orderBy('id', 'DESC')->paginate($limit);

        return response()->json([
            'data'=> $activityDetails
        ]);
    }
}
