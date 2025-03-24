<?php

namespace App\Helpers;

use Illuminate\Support\Arr;

class PaginationHelper
{
    protected $module;
    protected $page;
    protected $per_page;
    protected $total_page;

    public function __construct($module, $page, $per_page, $total_page)
    {
        $this->module = $module;
        $this->page = $page;
        $this->per_page = $per_page;
        $this->total_page = $total_page;
    }

    private function baseLink()
    {
        return "http://".env("APP_URL")."/api/".$this->module;
    }

    private function generatePageLink($page)
    {
        return $this->baseLink() . "?per_page=" . $this->per_page . "&page=" . $page;
    }

    private function generatePaginationObject($page )
    {
        $paginationObject = [
            "title" => $page,
            "link" => null,
            "active" => false,
        ];
    
        if ($page === "Prev") {
            $paginationObject["link"] = $this->page > 1 
                ? $this->generatePageLink($this->page - 1) 
                : null;
        } elseif ($page === "Next") {
            $paginationObject["link"] = $this->page + 1 <= $this->total_page 
                ? $this->generatePageLink($this->page + 1) 
                : null;
        } else {
            $paginationObject["link"] = $this->generatePageLink($page);
            $paginationObject["active"] = $page !== "..." && $page == $this->page;
        }
    
        return $paginationObject;
    }

    private function appendNextAndPreviousObject($pagination)
    {
        $prev =  $this->generatePaginationObject(page: "Prev");
        $next =  $this->generatePaginationObject(page: "Next");
        
        $pagination = Arr::prepend($pagination, $prev);
        array_push($pagination, $next);

        return $pagination;
    }

    private function isLastPage()
    {
        return $this->total_page - $this->per_page * $this->page;
    }

    private function pageContentToRender()
    {
        return $this->per_page * $this->page;
    }

    public function create()
    {
        $pagination = [];

        // Render all pagination number if page is less than 10
        if($this->total_page < 10){
            $pagination_number_to_render = range(1,$this->total_page);
            

            // generate pagination number object attach 
            foreach($pagination_number_to_render as $paginationNumber){
                
                if((int) $paginationNumber === $this->total_page - 1){
                    $pagination[] = $this->generatePaginationObject(page: "...");
                }
                
                $pagination[] =  $this->generatePaginationObject(page: $paginationNumber);
            }
        }

        if($this->total_page > 10)
        {
            $pagination_number_to_render = 0;

            // render all pagination number if page reach to end
            if($this->isLastPage() < 10){
                $start = $this->pageContentToRender();
                $end = $this->isLastPage();

                $pagination_number_to_render = range($start, $end);
            }else{
                /**
                 * Pagination number of current page, display start to (n) - 3
                 * display Pagination number of 2nd and last page
                 */
                $end_pagination = [$this->total_page-1, $this->total_page];
                $start = $this->pageContentToRender();
                $end = $this->isLastPage();

                $start_pagination = range($start, $end - 3);
                $pagination_number_to_render = array_merge($start_pagination, $end_pagination);
            }

            // generate pagination number object attach 
            foreach($pagination_number_to_render as $paginationNumber){
                
                if((int) $paginationNumber === $this->total_page - 1){
                    $pagination[] = $this->generatePaginationObject(page: "...");
                }
                
                $pagination[] =  $this->generatePaginationObject(page: $paginationNumber);
            }
        }

        return $this->appendNextAndPreviousObject($pagination);
    }

    public function prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $next_last_id)
    {
        $prev = [
            "title" => 'previous',
            "link" => $next_last_id !== 0? url()->current() . "?search={$search}&per_page={$per_page}&last_initial_id={$next_last_id}&last_id={$last_initial_id}": null,
            "is_active" => false
        ];

        $pagination = Arr::prepend($pagination, $prev);

        return $pagination;
    }

    protected function appendNextSearchPagination($pagination, $next)
    {
        return array_push($pagination, $next);
    }
    
    public function createSearchPagination($initial, $chunks, $search, $per_page, $lastInitialId)
    {
        $pages = [];

        for($x = 0; $x < count($chunks); $x++)
        {
            $pages[] = [
                'index' =>  $x + 1,
                'first_id' => $chunks[$x]->first(),
                'last_id' => $x > 0 ? $chunks[$x - 1]->last(): 0,
            ];
        }

        $nextIndex = null;

        $pagination = [];
        $last_initial_id = $initial === 0? $initial : $lastInitialId;
        $max_item = $initial + 4;

        for($i = $initial; $i < count($pages); $i++){
            $last_id = $pages[$i]['last_id'];

            $page_item = [
                'title' => $pages[$i]['index'],
                'link' =>  url()->current() . "?search={$search}&per_page={$per_page}&last_initial_id={$last_initial_id}&last_id={$last_id}",
                'is_active' => $i === $initial? true: false
            ];

            if($i === $max_item && count($pages) > $max_item + 2){
                $page_item = count($pagination);
                // $last_id = $pages[$i + 1]['last_id'];
                // $last_initial_id = $pages[$i]['first_id'];

                $page_item = [
                    'title' => "...",
                    'link' =>  url()->current() . "?search={$search}&per_page={$per_page}&last_initial_id={$last_initial_id}&last_id={$last_id}&page_item={$page_item}",
                    'is_active' => false
                ];

                $pagination[] = $page_item;

                $last_id = $pages[count($pages) - 1]['last_id'];
                $last_initial_id = $pages[count($pages) - 1]['first_id'];

                $last_page_item = [
                    'title' => $pages[count($pages) - 1]['index'],
                    'link' =>  url()->current() . "?search={$search}&per_page={$per_page}&last_initial_id={$last_initial_id}&last_id={$last_id}",
                    'is_active' => false
                ];

                $pagination[] = $last_page_item;
                break;
            }
            
            if($pages[$i]['index'] === 0 && $nextIndex == null){
                $nextIndex = $i + 1;
            }

            $pagination[] = $page_item;
            $last_initial_id = $pages[$i]['first_id'];
        }

        $next = [
            ...$pagination[0],
            'title' => 'next'
        ];

        $pagination[] = $next;

        return $pagination;
    }
}
