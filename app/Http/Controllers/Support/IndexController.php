<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\FrontController;

use App\Models\SupportCategory;
use App\Models\SupportQuestion;

use Validator;
use Response;
use Request;
use Image;
use App;

class IndexController extends FrontController {

	public function index($locale=null) {

		if(!empty($locale) && !in_array($locale, config('langs'))) {
			return redirect(getLangUrl('page-not-found'));
		}
		
		$curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->api_link.'/get-suppor-info/',
            CURLOPT_SSL_VERIFYPEER => 0
        ));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        if(!empty($resp) && isset($resp->success) && $resp->success) {

        	$all_questions = [];
        	if(!empty($resp->data->all_questions)) {

	        	foreach($resp->data->all_questions as $qs) {
	        		$all_questions[] = [
	        			'question' => $qs->question,
	        			'slug' => $qs->slug
	        		];
	        	}
        	}

			return $this->ShowView('index', [
				'categories' => $resp->data->categories,
				'main_questions' => $resp->data->main_questions,
				'all_questions' => addslashes(json_encode($all_questions)),
			]);
        } else {

			$all_questions = [];
        	foreach(SupportQuestion::get() as $qs) {
        		$all_questions[] = [
        			'question' => $qs->question,
        			'slug' => $qs->slug
        		];
        	}

        	return $this->ShowView('index', [
        		'categories' => SupportCategory::with('questions')->orderBy('order_number', 'asc')->get(),
                'main_questions' => SupportQuestion::where('is_main', 1)->get(),
                'all_questions' => addslashes(json_encode($all_questions)),
			]);
        }
	}

	public function contact($locale=null) {

		if(Request::isMethod('post')) {

			if(!empty($this->user) && $this->user->id == 37530) {
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_POST => 1,
					CURLOPT_URL => $this->api_link.'/contact-check-existing/',
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_POSTFIELDS => array(
						'user_id' => $this->user->id
					)
				));

				$resp = json_decode(curl_exec($curl));
				curl_close($curl);

				if(!empty($resp->success)) {

					if(!empty($resp->existing)) {
						return Response::json( [
							'success' => false,
							'messages' => 'It looks like you have already contacted our Support team. Kindly, wait until we get back to you.'
						]);
					}
				}
			}

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

            	if(empty($this->user) && Request::input('issue') != 'login') {

	                return Response::json( [
	                	'success' => false,
	                    'need_login' => true,
	                ] );
            	}

            	if(!$this->validateLatin(Request::input('description'))) {
            		 return Response::json( [
	                	'success' => false,
	                    'non_latin' => true,
	                ] );
            	}

            	$captcha = false;
	            $cpost = [
	                'secret' => env('CAPTCHA_SECRET'),
	                'response' => Request::input('g-recaptcha-response'),
	                'remoteip' => !empty($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : Request::ip()
	            ];
	            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
	            curl_setopt($ch, CURLOPT_HEADER, 0);
	            curl_setopt ($ch, CURLOPT_POST, 1);
	            curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($cpost));
	            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
	            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	            $response = curl_exec($ch);
	            curl_close($ch);

	            if($response) {
	                $api_response = json_decode($response, true);
	                if(!empty($api_response['success'])) {
	                    $captcha = true;
	                }
	            }
	            if( !$captcha ) {
	                $ret = array(
	                    'success' => false,
	                    'error_captcha' => true,
	                );

	                return Response::json( $ret );
	            }

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

		return $this->ShowView('contact', [
			'seo_title' => 'Contact Support | Dentacoin',
			'seo_description' => 'Get in touch with Dentacoin support team.',
			'social_title' => 'Contact Support | Dentacoin',
			'social_description' => 'Get in touch with Dentacoin support team.',
			'js' => [
				'contact.js',
			],
		]);
	}

    private function validateLatin($string) {
        $result = false;
     
        if (preg_match("/^[\w\d\s\+\'\&.,!?()-’]*$/", $string)) {
            $result = true;
        }
     
        return $result;
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

        if(!empty($resp) && isset($resp->success) && $resp->success) {

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

	        	$seo_description = explode('.', trim(preg_replace('/\s\s+/', ' ', strip_tags($resp->data->question->content))));

				return $this->ShowView('question', [
					'question' => $resp->data->question,
					'categories' => $resp->data->categories,
					'main_questions' => $resp->data->main_questions,
					'all_questions' => addslashes(json_encode($all_questions)),
					'seo_title' => $resp->data->question->question.' | Dentacoin',
					'seo_description' => isset($seo_description[1]) ? $seo_description[0].'.'.$seo_description[1] : $seo_description[0],
					'social_title' => $resp->data->question->question.' | Dentacoin',
					'social_description' => isset($seo_description[1]) ? $seo_description[0].'.'.$seo_description[1] : $seo_description[0],
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

	        	$seo_description = explode('.', trim(preg_replace('/\s\s+/', ' ', strip_tags($question->content))));

	        	return $this->ShowView('question', [
	        		'question' => $question,
	                'categories' => SupportCategory::with('questions')->orderBy('order_number', 'asc')->get(),
	                'main_questions' => SupportQuestion::where('is_main', 1)->get(),
	                'all_questions' => addslashes(json_encode(SupportQuestion::get())),
					'seo_title' => $question->question.' | Dentacoin',
					'seo_description' => isset($seo_description[1]) ? $seo_description[0].'.'.$seo_description[1] : $seo_description[0],
					'social_title' => $question->question.' | Dentacoin',
					'social_description' => isset($seo_description[1]) ? $seo_description[0].'.'.$seo_description[1] : $seo_description[0],
				]);
			} else {
        		return redirect(getLangUrl('/'));
        	}
        }
	}
}