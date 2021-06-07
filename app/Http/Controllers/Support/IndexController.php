<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\FrontController;
// use Illuminate\Support\Facades\Request;

use App\Models\SupportCategory;
use App\Models\SupportQuestion;

use Validator;
use Response;
use Request;
use Image;
use App;

class IndexController extends FrontController {

	public function index($locale=null) {

		App::setLocale('en');
		
		// $curl = curl_init();
  //       curl_setopt_array($curl, array(
  //           CURLOPT_RETURNTRANSFER => 1,
  //           CURLOPT_URL => $this->api_link.'/get-suppor-info/',
  //           CURLOPT_SSL_VERIFYPEER => 0
  //       ));
  //       curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  //       $resp = json_decode(curl_exec($curl));
  //       curl_close($curl);

  //       if(!empty($resp) && $resp->success) {

  //       	$all_questions = [];
  //       	if(!empty($resp->data->all_questions)) {

	 //        	foreach($resp->data->all_questions as $qs) {
	 //        		$all_questions[] = [
	 //        			'question' => $qs->question,
	 //        			'slug' => $qs->slug
	 //        		];
	 //        	}
  //       	}

		// 	return $this->ShowSupportView('index', [
		// 		'categories' => $resp->data->categories,
		// 		'main_questions' => $resp->data->main_questions,
		// 		'all_questions' => json_encode($all_questions),
		// 	]);
  //       } else {

			$all_questions = [];
        	foreach(SupportQuestion::get() as $qs) {
        		$all_questions[] = [
        			'question' => $qs->question,
        			'slug' => $qs->slug
        		];
        	}

        	return $this->ShowSupportView('index', [
        		'categories' => SupportCategory::with('questions')->get(),
                'main_questions' => SupportQuestion::where('is_main', 1)->get(),
                'all_questions' => json_encode($all_questions),
			]);
        // }
	}

	public function contact($locale=null) {

		if(Request::isMethod('post')) {
		    $all_ext = ['png', 'jpg', 'jpeg',      'mp4', 'm3u8', 'ts', 'mov', 'avi', 'wmv', 'qt'];

			$validator = Validator::make(Request::all(), [
                'issue' => array('required'),
                'email' => array('sometimes', 'required', 'email'),
                'platform' => array('required'),
                'description' => array('required', 'min:4'),
                'file' => array('required', 'file', 'mimes:'.implode(',', $all_ext), 'max:10000000'),
            ]);


            if ($validator->fails()) {

                $msg = $validator->getMessageBag()->toArray();
                $ret = array(
                    'success' => false,
                    'messages' => array()
                );

                foreach ($msg as $field => $errors) {
                    $ret['messages'][$field] = implode(', ', $errors);
                }

                return Response::json( $ret );
            } else {
            	// $captcha = false;
	            // $cpost = [
	            //     'secret' => env('CAPTCHA_SECRET'),
	            //     'response' => Request::input('g-recaptcha-response'),
	            //     'remoteip' => !empty($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : Request::ip()
	            // ];
	            // $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
	            // curl_setopt($ch, CURLOPT_HEADER, 0);
	            // curl_setopt ($ch, CURLOPT_POST, 1);
	            // curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($cpost));
	            // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
	            // curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	            // $response = curl_exec($ch);
	            // curl_close($ch);
	            // if($response) {
	            //     $api_response = json_decode($response, true);
	            //     if(!empty($api_response['success'])) {
	            //         $captcha = true;
	            //     }
	            // }
	            // if( !$captcha ) {
	            //     $ret = array(
	            //         'success' => false,
	            //         'error_captcha' => true,
	            //     );

	            //     return Response::json( $ret );
	            // }

        		$file_extension = Request::file('file')->extension();

        		if($file_extension == 'qt') {
        			$file_extension = 'mov';
        		}
				$image_ext = ['png', 'jpg', 'jpeg' ];
		        $video_ext = ['mp4', 'm3u8', 'ts', 'mov', 'avi', 'wmv', 'qt'];

		        $time = time();

		        $folder = storage_path().'/app/public/support-contact/'.($time%100);
		        if(!is_dir($folder)) {
		            mkdir($folder);
		        }

		        $to = $folder.'/'.$time.'.'.$file_extension;
		        $video_path = null;

		        if(in_array($file_extension, $image_ext )) {
		        	$img = Image::make( Request::file('file') )->orientate();

			        $img->resize(1920, null, function ($constraint) {
			            $constraint->aspectRatio();
			            $constraint->upsize();
			        });
			        $img->save($to);
		        } else {
		        	$file = Request::file('file');
		            $filename = $file->getClientOriginalName();
		            $file->move($folder, $filename);
		        	$video_path = $folder.'/'.$filename;
		        }

				$dir = $video_path ?? $folder.'/'.$time.'.'.$file_extension; // full directory of the file '/var/www/html/storage/test.zip'

				$cFile = curl_file_create($dir);
				$post = [
					'user_id' => !empty($this->user) ? $this->user->id : null,
					'email' => empty($this->user) ? request('email') : null,
					'platform' => request('platform'),
					'issue' => request('issue'),
					'description' => request('description'),
					'file' => curl_file_create($dir, \File::mimeType($dir), 'da'.$time.'.'.$file_extension),
				];

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->api_link.'/contact/');
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				$result=json_decode(curl_exec($ch));
				curl_close ($ch);

				exec('rm -R '.$folder);
				
				return Response::json( [
					'success' => isset($result->success) ? true : false,
				] );
            }
		}

		return $this->ShowSupportView('contact', [
			'js' => [
				'contact.js',
			],
		]);
	}

	private function getFilePath($thumb = false) {
        $folder = storage_path().'/app/public/support-contact/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.($thumb ? '-thumb' : '').'.jpg';
    }

	public function question($locale=null, $slug) {

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_POST => 1,
			CURLOPT_URL => $this->api_link.'/get-question/',
			CURLOPT_SSL_VERIFYPEER => 0,
		    CURLOPT_POSTFIELDS => array(
		        'slug' => $slug
		    )
		));
		$resp = json_decode(curl_exec($curl));
		curl_close($curl);

        if(!empty($resp) && $resp->success) {

        	if(!empty($resp->data->question)) {

        		$all_questions = [];
	        	if(!empty($resp->data->all_questions)) {

		        	foreach($resp->data->all_questions as $qs) {
		        		$all_questions[] = [
		        			'question' => $qs->question,
		        			'slug' => $qs->slug
		        		];
		        	}
	        	}
        	
				return $this->ShowSupportView('question', [
					'question' => $resp->data->question,
					'categories' => $resp->data->categories,
					'main_questions' => $resp->data->main_questions,
					'all_questions' => json_encode($all_questions),
				]);
        	} else {
        		return redirect(getLangUrl('/'));
        	}
        } else {

            $question = SupportQuestion::whereHas('translations', function ($query) use ($slug) {
                $query->where('locale', 'en')
                ->where('slug', 'LIKE', $slug);
            })->first();

	        if(!empty($question)) {
	        	return $this->ShowSupportView('question', [
	        		'question' => $question,
	                'categories' => SupportCategory::with('questions')->get(),
	                'main_questions' => SupportQuestion::where('is_main', 1)->get(),
	                'all_questions' => json_encode(SupportQuestion::get()),
				]);
			} else {
        		return redirect(getLangUrl('/'));
        	}
        }
	}
}