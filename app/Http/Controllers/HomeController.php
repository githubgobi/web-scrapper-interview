<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Auth;
use Hash;
use DB;
use Goutte;


use App\Models\Industry;
use App\Models\Link;
use App\Models\State;
use App\Models\Company;
use App\Models\CompanyInfo;
use App\Models\CompanyDirector;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(Request $request){
        if( $request->url!=''){
            $crawler = Goutte::request('GET', $request->url);
            // dd($crawler->filter('meta')->html());
            $checkLinkExists  =  Link::where('link',$request->url)->first();
            if(!$checkLinkExists){
                $checkLinkExists = new Link();
                $checkLinkExists->link = $request->url;
                $checkLinkExists->title = $crawler->filter('title')->text();
                $checkLinkExists->save();
            }
            // Second Hierachy Process
            $uri = parse_url( $request->url);
            if(isset($uri['path'])){
                $checkIndustries = Industry::where('link', 'LIKE', '%' . $uri['path'] . '%')->first();
                if($checkIndustries){
                    $value=1;
                $totalPagination = $crawler->filter('.pagination li')->each(function ($paginationNode ,$paginationIndexValue) use ($value){
                        if($paginationNode->text()>$value)
                            $value = $paginationNode->text();
                        return (int) $value;
                    });           
                        //   Need Use Queue System Load Pagination values
                    for($loop=1;$loop<=max($totalPagination);$loop++){
                        $innerCrawler = Goutte::request('GET', $request->url."/page/".$loop);
                        // dd($innerCrawler);
                        $innerCrawler->filter('.table-bordered tr')->each(function ($node ,$indexValue) use ($checkIndustries) {
                            if($indexValue!=0){
                                $checkCompany = Company::where('full_text',$node->text())->first();
                                if(!$checkCompany){
                                    $newCompany = new Company();
                                    $newCompany->industries_id = $checkIndustries->id;
                                    $newCompany->full_text = $node->text();
                                    $i=1;
                                    
                                    $node->filter('td')->each(function ($nodeElements,$index) use ($newCompany) {
                                        if($index==0){
                                            $newCompany->cin = $nodeElements->text();
                                        }
                                        if($index==1){
                                            $newCompany->name = $nodeElements->text();
                                            $newCompany->slug = Str::slug($nodeElements->text(), '-');
                                            $newCompany->link = $nodeElements->filter('a')->attr('href');
                                            // dd($nodeElements->filter('a')->attr('href'));
                                        }
                                        if($index==2){
                                            $newCompany->class = $nodeElements->text();
                                        }
                                        if($index==3){
                                            $newCompany->status = $nodeElements->text();
                                        }
                                    });
                                    $newCompany->save();
                                }
                            }                  
                        });    
                    }
                }
                // Third Hierachy Process
                $getCompanyValue = trim(str_replace("business","",str_replace("/","",$uri['path'])));            
                $checkCompany = Company::where('slug',$getCompanyValue)->first();
                if($checkCompany){
                    $innerCrawler = Goutte::request('GET', $request->url);                
                    $innerCrawler->filter('#companyinformation .table tbody tr')->each(function ($node ,$indexValue) use ($checkCompany) {
                        $companySlug = Str::slug($node->text(), '-');
                        
                        $checkCompanyInfo = CompanyInfo::where('slug',$companySlug)->first();
                        if(!$checkCompanyInfo){
                            $newCompanyInfo = new CompanyInfo();
                            $newCompanyInfo->company_id  = $checkCompany->id;
                            $newCompanyInfo->slug = $companySlug;
                            $node->filter('td')->each(function ($nodeElements,$index) use ($newCompanyInfo) {                            
                                if($index==0){
                                    $newCompanyInfo->title = $nodeElements->text();
                                }elseif($index==1){
                                    $newCompanyInfo->value = $nodeElements->text();
                                }
                            });
                            $newCompanyInfo->save();
                        }
                    });
                    $innerCrawler->filter('#contactdetails .table tbody tr')->each(function ($node ,$indexValue) use ($checkCompany) {
                        $companySlug = Str::slug($node->text(), '-');
                        $checkCompanyInfo = CompanyInfo::where('slug',$companySlug)->first();
                        if(!$checkCompanyInfo){
                            $newCompanyInfo = new CompanyInfo();
                            $newCompanyInfo->company_id  = $checkCompany->id;
                            $newCompanyInfo->slug = $companySlug;
                            $node->filter('td')->each(function ($nodeElements,$index) use ($newCompanyInfo) {                            
                                if($index==0){
                                    $newCompanyInfo->title = $nodeElements->text();
                                }elseif($index==1){
                                    $newCompanyInfo->value = $nodeElements->text();
                                }
                            });
                            $newCompanyInfo->save();
                        }
                    });
                    $innerCrawler->filter('#listingandannualcomplaincedetails .table tbody tr')->each(function ($node ,$indexValue) use ($checkCompany) {
                        $companySlug = Str::slug($node->text(), '-');
                        $checkCompanyInfo = CompanyInfo::where('slug',$companySlug)->first();
                        if(!$checkCompanyInfo){
                            $newCompanyInfo = new CompanyInfo();
                            $newCompanyInfo->company_id  = $checkCompany->id;
                            $newCompanyInfo->slug = $companySlug;
                            $node->filter('td')->each(function ($nodeElements,$index) use ($newCompanyInfo) {                            
                                if($index==0){
                                    $newCompanyInfo->title = $nodeElements->text();
                                }elseif($index==1){
                                    $newCompanyInfo->value = $nodeElements->text();
                                }
                            });
                            $newCompanyInfo->save();
                        }
                    });

                    $innerCrawler->filter('#otherinformation .table tbody tr')->each(function ($node ,$indexValue) use ($checkCompany) {
                        $companySlug = Str::slug($node->text(), '-');
                        $checkCompanyInfo = CompanyInfo::where('slug',$companySlug)->first();
                        if(!$checkCompanyInfo){
                            $newCompanyInfo = new CompanyInfo();
                            $newCompanyInfo->company_id  = $checkCompany->id;
                            $newCompanyInfo->slug = $companySlug;
                            $node->filter('td')->each(function ($nodeElements,$index) use ($newCompanyInfo) {                            
                                if($index==0){
                                    $newCompanyInfo->title = $nodeElements->text();
                                }elseif($index==1){
                                    $newCompanyInfo->value = $nodeElements->text();
                                }
                            });
                            $newCompanyInfo->save();
                        }
                    });

                    $innerCrawler->filter('#directors .table-responsive tbody tr')->each(function ($directorNode ,$indexValue) use ($checkCompany) {
                        if($indexValue!=0){
                            $companySlug = Str::slug($directorNode->text(), '-');
                            $checkCompanyDirector = CompanyDirector::where('slug',$companySlug)->first();
                            
                            if(!$checkCompanyDirector){
                                $newCompanyDirector = new CompanyDirector();
                                $newCompanyDirector->company_id  = $checkCompany->id;
                                $newCompanyDirector->slug = $companySlug;
                                $directorNode->filter('td')->each(function ($nodeElements,$index) use ($newCompanyDirector) {                            
                                    if($index==0){
                                        $newCompanyDirector->empid = $nodeElements->text();
                                    }elseif($index==1){
                                        $newCompanyDirector->empname = $nodeElements->text();
                                    }
                                    elseif($index==2){
                                        $newCompanyDirector->empdesignation = $nodeElements->text();
                                    }
                                    elseif($index==3){
                                        $newCompanyDirector->dateofappointment = $nodeElements->text();
                                    }
                                });
                                // $newCompanyDirector->save();
                            }
                        }
                    });
                    
                    $innerCrawler->filter('#faq .panel-default')->each(function ($node ,$indexValue) use ($checkCompany) {                    
                        $companySlug = Str::slug($node->text(), '-');
                        $checkCompanyInfo = CompanyInfo::where('slug',$companySlug)->first();
                        if(!$checkCompanyInfo){
                            $newCompanyInfo = new CompanyInfo();
                            $newCompanyInfo->company_id  = $checkCompany->id;
                            $newCompanyInfo->slug = $companySlug;
                            $node->filter('.panel-heading')->each(function ($nodeElements,$index) use ($newCompanyInfo) {                            
                                if($index==0){
                                    $newCompanyInfo->title = $nodeElements->text();
                                }
                            });
                            $node->filter('.panel-body')->each(function ($nodeElements,$index) use ($newCompanyInfo) {                            
                                if($index==0){
                                    $newCompanyInfo->value = $nodeElements->text();
                                }
                            });
                            $newCompanyInfo->save();
                        }
                    });
                }
            }
            // First Hierachy Process
            $crawler->filter('.list-group-item a')->each(function ($node) use ($checkLinkExists) {
                if (strpos($node->attr('href'), 'industry/section') !== false) {
                        $checkIndustryExists  =  Industry::where('title',$node->text())->first();
                        if(!$checkIndustryExists){
                            $industry = new Industry();
                            $industry->link_id = $checkLinkExists->id;
                            $industry->link = $node->attr('href');
                            $industry->title = $node->text();
                            $industry->save();
                        }
                }
                if (strpos($node->attr('href'), '/state/') !== false) {
                    $checkIndustryExists  =  State::where('states',$node->text())->first();
                    if(!$checkIndustryExists){
                        $state = new State();
                        $state->link_id = $checkLinkExists->id;
                        $state->states = $node->text();
                        $state->link = $node->attr('href');
                        $state->save();
                    }
                }
            });
        }
        return view('welcome');	
    }
}
