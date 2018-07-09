<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Lend;
use TCPDF;

class LendController extends Controller
{
    public function test(){
        $config = session('config');
        if(empty($config['code'])){
            return '没有登录';
        }
        $url = self::PUBLIC_OPTION.'user/verify_me';
        $param = array();
        $param['access_token'] = $config['info']['access_token'];
        $arr = $this->queryURL($url, $param, false);
        $institute = $arr['info']['yb_collegename'];
        return dd($arr);
    }

    public function show(){
        $time = Carbon::now();
        $year = $time->year;
        $month = $time->month;
        $date = $time->day;
        $hour = $time->hour;
        $minute = $time->minute;

        $lend = Lend::where('year', '>', $year)
            ->orWhere(function($query1)use($year, $month, $date, $hour, $minute){
                $query1->where('year', '=', $year)
                    ->where('month', '>', $month)
                    ->orWhere(function($query2)use($month, $date, $hour, $minute){
                        $query2->where('month', '=', $month)
                            ->where('date', '>', $date)
                            ->orWhere(function($query3)use($date, $hour, $minute){
                                $query3->where('date', '=', $date)
                                    ->where('start_hour', '>', $hour)
                                    ->orWhere(function($query4)use($hour, $minute){
                                        $query4->where('start_hour', '=', $hour)
                                            ->where('start_minute', '>', $minute);
                                    });
                            });
                    });
            })
            ->get()->toArray();
        return response()->json($lend);
    }


    public function check(){
        $time = Carbon::now();
        $year = $time->year;
        $month = $time->month;
        $date = $time->day;
        $hour = $time->hour;
        $minute = $time->minute;

        $config = session('config');
        $id = $config['info']['userid'];
        $lend = Lend::where('personId', '=', $id)
            ->where(function($Query)use($year, $month, $date, $hour, $minute){
                $Query ->where('year', '>', $year)
                    ->orWhere(function($query1)use($year, $month, $date, $hour, $minute){
                        $query1->where('year', '=', $year)
                            ->where('month', '>', $month)
                                ->orWhere(function($query2)use($month, $date, $hour, $minute){
                                $query2->where('month', '=', $month)
                                    ->where('date', '>', $date)
                                        ->orWhere(function($query3)use($date, $hour, $minute){
                                            $query3->where('date', '=', $date)
                                            ->where('start_hour', '>', $hour)
                                                ->orWhere(function($query4)use($hour, $minute){
                                                    $query4->where('start_hour', '=', $hour)
                                                        ->where('start_minute', '>', $minute);
                                                });
                                        });
                                });
                    });
            })
            ->get()->toArray();
        return $lend;
    }

    /**
     * @param Request $request
     * @return int -1申请重复，0申请失败，1申请成功
     */
    public function create(Request $request){
        $arr1 = explode('-', request('year_date'));
        $arr2 = explode(':', request('start_time'));
        $arr3 = explode(':', request('end_time'));

        // 判断是否有重合申请
        $lend = Lend::where('year', $arr1[0])
            ->where('month', $arr1[1])
            ->where('date', $arr1[2])
            ->where(function($query)use($arr2, $arr3){
                $query->where(function($query1)use($arr2){ //开始时间是否在已申请区间内
                    $query1->where(function($query11)use($arr2){
                        $query11->where('start_hour', '<', $arr2[0])
                            ->orWhere(function($query111)use($arr2){
                                $query111->where('start_hour', '=', $arr2[0])
                                    ->where('start_minute', '<=', $arr2[1]);
                            });
                    })->where(function($query12)use($arr2){
                        $query12->where(function($query122)use($arr2){
                            $query122->where('end_hour', '>', $arr2[0])
                                ->orWhere(function($query1222)use($arr2){
                                    $query1222->where('end_hour', '=', $arr2[0])
                                        ->where('end_minute', '>=', $arr2[1]);
                                });
                        });
                    });
                })->orWhere(function($query2)use($arr3){ //结束时间是否在已申请区间内
                    $query2->where(function($query21)use($arr3){
                        $query21->where('start_hour', '<', $arr3[0])
                            ->orWhere(function($query211)use($arr3){
                                $query211->where('start_hour', '=', $arr3[0])
                                    ->where('start_minute', '<=', $arr3[1]);
                            });
                    })->where(function($query22)use($arr3){
                        $query22->where(function($query222)use($arr3){
                            $query222->where('end_hour', '>', $arr3[0])
                                ->orWhere(function($query2222)use($arr3){
                                    $query2222->where('end_hour', '=', $arr3[0])
                                        ->where('end_minute', '>=', $arr3[1]);
                                });
                        });
                    });
                })->orWhere(function($query3)use($arr2, $arr3){ //时间是否包含已申请时间
                    $query3->where(function($query31)use($arr2){ //开始时间
                        $query31->where('start_hour', '>', $arr2[0])
                            ->orWhere(function($query311)use($arr2){
                                $query311->where('start_hour', '=', $arr2[0])
                                    ->where('start_minute', '>=', $arr2[1]);
                            });
                    })->where(function($query32)use($arr3){ //结束时间
                        $query32->where('end_hour', '<', $arr3[0])
                            ->orWhere(function($query322)use($arr3){
                                $query322->where('end_hour', '=', $arr3[0])
                                    ->where('end_minute', '<=', $arr3[1]);
                            });
                    });
                });
            })
            ->get();


        if($lend->count()>0){
            return dd($lend->toArray());
        }

        $lend = new Lend;
        $lend->classroom = request('classroom');
        $lend->personName = request('personName');
        $lend->personId = request('personId');
        $lend->phone = request('phone');
        $lend->org = request('org');
        $lend->reason = request('reason');
        $lend->year = $arr1[0];
        $lend->month = $arr1[1];
        $lend->date = $arr1[2];
        $lend->start_hour = $arr2[0];
        $lend->start_minute = $arr2[1];
        $lend->end_hour = $arr3[0];
        $lend->end_minute = $arr3[1];
        $lend->pass = 0;

        if(!$lend->save()){
            return 0;
        }
        return 1;
    }

    public function sentCaptcha(){
        return captcha();
    }

    public function loadPdf(Request $request){
        $pdf = new TCPDF();
        // 设置文档信息
        $pdf->SetCreator('');
        $pdf->SetAuthor('');
        $pdf->SetTitle('人文课室借用');
        $pdf->SetSubject('');
        $pdf->SetKeywords('TCPDF, PDF, PHP');


        // 设置页眉和页脚信息
        $pdf->SetHeaderData('tcpdf_logo.jpg', 30, '华南农业大学', '人文场地借用申请表单', [0, 64, 255], [0, 64, 128]);
        $pdf->setFooterData([0, 64, 0], [0, 64, 128]);
//
//        // 设置页眉和页脚字体
        $pdf->setHeaderFont(['stsongstdlight', '', '10']);
        $pdf->setFooterFont(['helvetica', '', '8']);

        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // 设置间距
        $pdf->SetMargins(15, 15, 15);//页面间隔
        $pdf->SetHeaderMargin(5);//页眉top间隔
        $pdf->SetFooterMargin(10);//页脚bottom间隔

        // 设置分页
        $pdf->SetAutoPageBreak(true, 25);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        //设置字体 stsongstdlight支持中文
        $pdf->SetFont('stsongstdlight', '', 14);

        //第一页
        $pdf->AddPage();
        $lend = Lend::find($request->input('id'));
        if(empty($lend)){
            $html = '<p>没有对应的借用申请信息，非常抱歉^_^;</p>';
            return $pdf->Output('t.pdf', 'I');
        }
        $personName = $lend->personName;
        $phone = $lend->phone;
        $classroom = $lend->classroom;
        $org = $lend->org;
        $reason = $lend->reason;
        $time = Carbon::create($lend->year, $lend->month, $lend->date, $lend->start_hour, $lend->start_minute);
        $start_time = $time->toDateTimeString();
        $time->hour = $lend->end_hour;
        $time->minute = $lend->end_minute;
        $end_time = $time->toDateTimeString();

        $html = '<br><p>申请人：'.$personName.'<p/>';
        $html .= '手机号：'.$phone.'<br/><br/>';
        $html .= '申请课室：'.$classroom.'<br/><br/>';
        $html .= '申请单位：'.$org.'<br/><br/>';
        $html .= '开始时间：'.$start_time.'<br/><br/>';
        $html .= '结束时间：'.$end_time.'<br/><br/>';
        $html .= '活动内容：'.$reason.'<br/><br/>';
        $pdf->writeHTML($html);

        //输出PDF
        $pdf->Output('t.pdf', 'I');//I输出、D下载
    }

    public function judge(Request $request){
        //TODO: 判断是不是管理员
        $pass = request('pass');
        $id = request('id');
        $lend = Lend::find($id);
        $lend->pass = $pass;
        $lend->update();
        return 1;
    }

}
