<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Query;
use App\Models\Question;
use App\Models\Answer;
use App\Models\UserApiAccess;
use App\Models\ApiDetail;
use App\Models\QuizDesign;
use App\Models\Possibility;



class RegisterController extends BaseController
{
  
     private function getRandomString($length) {
       $characters = '0123456789';
       $string = '';

       for ($i = 0; $i < $length; $i++) {
         $string .= $characters[mt_rand(0, strlen($characters) - 1)];
     }

      return $string;
   }
   
  
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            'email' => 'required|email',
            'password' => 'required'
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $admin = Admin::where('email', $request->email)->first();
        if($admin){
            if($admin->password == $request->password){
                $success['admin_id'] =  $admin->id;
                $success['email'] =  $admin->email;
       
                return $this->sendResponse($success, 'Admin login successfully.');

            }else{
                return $this->sendError('Unauthorised.', ['error'=>'Wrong Password']); 
            }

        }else{
            return $this->sendError('Unauthorised.', ['error'=>'Email does not exist']);  
        }
        
    }

    
    public function addQuiz(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'quiz_name' => 'required',
          //  'quiz_image' => 'required'
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $quiz_check = Quiz::where('quiz_name', $request->quiz_name)->first();
        if($quiz_check){
            return $this->sendError('Unauthorised.', ['error'=>'Quiz name already exist']); 
        }

        $input = $request->all();
       if(!empty($request->quiz_image)){ 
        $destinationPath = base_path('images');
        $quiz_image = time().'.'.$request->quiz_image->getClientOriginalExtension();

        $request->quiz_image->move($destinationPath, $quiz_image); 
        $input['quiz_image'] = $quiz_image;
       }
        $success = Quiz::create($input);
        return $this->sendResponse($success, 'Quiz added successfully.');
    }

    public function updateQuiz(Request $request){
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required',
            'quiz_name' => 'required'
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $data = [
            'quiz_name' => $request->quiz_name
        ];
        if(!empty($request->quiz_image)){

            $destinationPath = base_path('images');
            $quiz_image = time().'.'.$request->quiz_image->getClientOriginalExtension();
            
            $request->quiz_image->move($destinationPath, $quiz_image); 
            $data['quiz_image'] = $quiz_image;
        } 
        $res = Quiz::where('id', $request->quiz_id)->update($data);
        $success = quiz::where('id', $request->quiz_id)->first();
        return $this->sendResponse($success, 'Quiz updated successfully.');
    }

    public function deleteQuiz(Request $request){
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required',
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $data = [
            'status' => '0'
        ];
        $res = Quiz::where('id', $request->quiz_id)->update($data);
        $success = quiz::where('id', $request->quiz_id)->first();
        return $this->sendResponse($success, 'Quiz deleted successfully.');  
    }

    public function fetchQuiz(Request $request){
        $where = ['status' => '1'];
       // $success = Quiz::where($where)->get()->toArray();
        $success = Quiz::where($where)->orderBy('id','desc')->get()->toArray();
        return $this->sendResponse($success, 'Quiz fetched successfully.');
    }

    public function addUser(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'group_name' => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:users',
           // 'categories_of_interest' => 'required',
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input = $request->all();
        $input['password'] = $request->first_name.getRandomString(4);
        $success = User::create($input);
        return $this->sendResponse($success, 'User registered successfully.');
    }

    public function userLogin(Request $request){
        $validator = Validator::make($request->all(), [
            
            'email' => 'required|email',
            'password' => 'required'
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $user = User::where('email', $request->email)->first();
        if($user){
            if($user->password == $request->password){
                $success['user_id'] =  $user->id;
                $success['email'] =  $user->email;
                $success['first_name'] =  $user->first_name;
                $success['last_name'] =  $user->last_name;
                $success['group_name'] =  $user->group_name;
                $success['phone'] =  $user->phone;
                $success['categories_of_interest'] =  $user->categories_of_interest;
                $success['address'] =  $user->address;
                $success['description'] =  $user->description;
       
                return $this->sendResponse($success, 'User login successfully.');

            }else{
                return $this->sendError('Unauthorised.', ['error'=>'Wrong Password']); 
            }

        }else{
            return $this->sendError('Unauthorised.', ['error'=>'Email does not exist']);  
        }
    }

    public function fetchUserQuiz(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $success = Quiz::where('user_id', $request->user_id)->where('status', '1')->orderBy('id','desc')->get()->toArray();
        return $this->sendResponse($success, 'Users quiz fetched successfully.');
    }

    public function fetchAllUsers(Request $request){
       // $success = User::orderBy('id','desc')->get()->toArray();
        $success = DB::table('users')
            ->select('users.*', DB::raw('COUNT(quizzes.user_id) AS quiz_count'))
            ->leftjoin('quizzes', 'quizzes.user_id', '=', 'users.id')
            ->groupBy('users.id')
            ->get()->toArray();
        return $this->sendResponse($success, 'Users and their total quiz fetched successfully.');
    }

    public function submitQuery(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'phone' => 'required',
            'message' => 'required',
        ]); 

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input = $request->all();
        $success = Query::create($input);
        return $this->sendResponse($success, 'Query submitted successfully.');
    }

    public function addQuestions(Request $request){
       // print_r($request->data);die;
        foreach($request->data as $val){
            $data = [
                'quiz_id' => $request['quiz_id'],
                'question' => $val['title'],
                'answer' => $val['sampleAnswer'],
                'input_type' => $val['type']
            ];
            $success = Question::create($data);
         if($success){
            foreach ($val['options'] as $option) {
                $answersdata = [
                    'question_id' => $success->id,
                    'answer' => $option['title'],
                    'score' => $option['score'],
                ];
              $result = Answer::create($answersdata);     
                
            }    
        }
        }
        return $this->sendResponse($success, 'Question submitted successfully.');
    }

    public function shareQuiz(Request $request,$id){
        $quiz = Quiz::where('id', $id)->first();
        
        $quiz_questions = Question::where('quiz_id', $quiz->id)->get()->toArray();
        $questioncount = count($quiz_questions);

        $questiondata = [
            'quiz_id' => $quiz->id,
            'quiz_name' => $quiz->quiz_name, 
            'question_count' => $questioncount,  
        ];
         
        $result = [$questiondata]; 
        foreach ($quiz_questions as $val) {
            $answer_details = Answer::where('question_id', $val['id'])->get()->toArray();
            $question_details = [
                'question_id' => $val['id'],
                'quiz_id' => $val['quiz_id'],
                'question_title' => $val['question'],
                'sample_answer' => $val['answer'],
                'input_type' => $val['input_type'],
                'answer_details' => $answer_details,
            ];
        
            foreach ($result as &$item) {
                if ($item['quiz_id'] === $question_details['quiz_id']) {
                    $item['question_details'][] = $question_details;
                }
            }
        }
      //  print_r($result);die;
        return $this->sendResponse($result, 'Quiz question fetched successfully.');
    }

    public function userAccess(Request $request){
        $userid = $request->userid;
        $access = [
          'access_level' =>  $request->access,
        ];
        $res = User::where('id', $userid)->update($access);
        return $this->sendResponse($res, 'Users access updated successfully.');
    }

    public function userApiAccess(Request $request){
        $userid = $request->user_id;
        $data = [
          'user_id' =>  $userid,
          'api_request' =>  '1',
          'status' => '1',
        ];
        $res = UserApiAccess::create($data);
        if($res){
            $update = [
                'api_access' => '1'
            ];
           $result = User::where('id',$userid)->update($update); 
        }
        return $this->sendResponse($res, 'Api access request successfull. Please check the Api Access section in My Profile.');  
    }

    public function fetchQuizTitle(Request $request){
        $res = Quiz::where('id', $request->quiz_id)->get()->toArray();
        //print_r($res[0]['quiz_name']);
        $result = [
            'quiz_title' => $res[0]['quiz_name']
        ];
        return $this->sendResponse($result, 'Quiz title fetched successfully.'); 
    }

    public function userProfile(Request $request){
        $res = user::where('id', $request->user_id)->get()->toArray();
       
        return $this->sendResponse($res, 'user profile fetched successfully.');  
    }

    public function userApiDetails(Request $request){
        $res = user::where('id', $request->user_id)->first(); 
       
        if($res->api_access == '1'){
            $success = ApiDetail::orderBy('id', 'asc')->get()->toArray();
            return $this->sendResponse($success, 'Api details fetched successfully.');  
        }else{
            $success = null;
            return $this->sendResponse($success, 'You have no api access.');   
        }
    }

    public function changePassword(Request $request){
        $res = User::where('id',$request->user_id)->first();
        if($request->old_password == $res->password){
            $data = [
                'password' => $request->new_password
            ];
    
            $result = User::where('id',$request->user_id)->update($data); 
            return $this->sendResponse($result, 'You have successfully updated your password.'); 
        }else{
            $result = null;
            return $this->sendResponse($result,'Current password is wrong');  
        }
         
    }

    public function submitQuizDesign(Request $request){
        $userid = $request->user_id;
        $dataarray = $request->dataarray;
        
            $data = [
                'user_id' => $userid,
                'quiz_id' => $dataarray['quizid'],
                'title_font' => $dataarray['titlefont'],
                'main_font' => $dataarray['mainfont']
            ];
        
        $res = QuizDesign::create($data); 
        return $this->sendResponse($res, 'Quiz design saved successfully.');    
        
    }

    public function addPossibilities(Request $request){
        // print_r($request->data);die;
         foreach($request->data as $val){
            $data = [
                'possibility' => $val['Possibilitytitle'],
                'description' => $val['Description'],
                'weblink' => $val['WebLink'],
                'assigned_quiz' => $val['AssignedQuizzes'],
                'active_inactive' => $val['ActiveOrInactive']
            ];
            $success = Possibility::create($data);
         if($success){
            foreach ($val['options'] as $option) {
                $answersdata = [
                    'possibility_id' => $success->id,
                    'answer' => $option['title'],
                    'score' => $option['score'],
                ];
              $result = Answer::create($answersdata);     
                
            }    
        }
        }
        return $this->sendResponse($success, 'Possibilities submitted successfully.');
    }
}
