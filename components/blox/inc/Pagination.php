<?php  
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @author		Rick Ellis. adopted to MODx by Amir Marcovitz
 * @copyright	Copyright (c) 2006, EllisLab, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Pagination Class
 *
 * @category	Pagination
 * @author		Rick Ellis. adopted to MODx by Amir Marcovitz
 * @link		http://www.codeigniter.com/user_guide/libraries/pagination.html
 */
class Pagination {

	var $base_id            = ''; // The id of the page we are linking to
	var $base_params        = ''; // further URL params
	var $total_rows  		= ''; // Total number of items (database results)
	var $per_page	 		= 10; // Max number of items you want shown per page
	var $num_links			= 2; // Number of "digit" links to show before/after the currently viewed page
	var $cur_item 			= 1;
	var $first_link   		= '&lsaquo; First';
	var $next_link			= '&gt;';
	var $prev_link			= '&lt;';
	var $last_link			= 'Last &rsaquo;';
	var $uri_segment		= 3;
	var $full_tag_open		= '';
	var $full_tag_close		= '';
	var $first_tag_open		= '';
	var $first_tag_close	= '&nbsp;';
	var $last_tag_open		= '&nbsp;';
	var $last_tag_close		= '';
	var $cur_tag_open		= '<span id="currentPage">&nbsp;';
	var $cur_tag_close		= '</span>';
	var $next_tag_open		= '&nbsp;';
	var $next_tag_close		= '&nbsp;';
	var $prev_tag_open		= '&nbsp;';
	var $prev_tag_close		= '';
	var $num_tag_open		= '&nbsp;';
	var $num_tag_close		= '';	

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	function Pagination($params = array())
	{
		if (count($params) > 0)
		{
			$this->initialize($params);		
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Initialize Preferences
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */
	function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}		
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
   * Generate the pagination links
   *
   * @access	public
   * @return	string
   */	
  function create_links()
  {
  		

		global $modx;
	    // If our item count or per-page total is zero there is no need to continue.
		if ($this->total_rows == 0 OR $this->per_page == 0)
		{
		    return '';
		}
		// Calculate the total number of pages
	 	$num_pages = ceil($this->total_rows / $this->per_page);
		
		$curId = ($this->base_id == '') ? $modx->resource->get('id') : $this->base_id;

		if (!is_array($this->base_params)) {
			if ($this->base_params != '') {
				parse_str($this->base_params);
			} else {
				$this->base_params = array();
			}
		}

		if ( ! is_numeric($this->cur_item))
		{
			$this->cur_item = 1;
		}
		
	
	//	

		// Is there only one page? Hm... nothing more to do here then.
		if ($num_pages == 1)
		{
			return '';
		}
		// Is the page number beyond the result range?
		// If so we show the last page
		if ($this->cur_item > $this->total_rows)
		{
			$this->cur_item = ($num_pages - 1) * $this->per_page;
		}

		$this->curPage = floor(($this->cur_item/$this->per_page) + 1);

		
		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = (($this->curPage - $this->num_links) > 0) ? $this->curPage - $this->num_links : 1;
		$end   = (($this->curPage + $this->num_links) < $num_pages) ? $this->curPage + $this->num_links : $num_pages;
		
  		// And here we go...
		$output = '';

		// Render the "First" link
		if  ($this->curPage > $this->num_links)
		{
			$curParams = array_merge($this->base_params, array('pagestart' => 1));
			$output .= $this->first_tag_open.'<a href="' . $modx->makeUrl($curId, '', $curParams) . '">'.$this->first_link.'</a>'.$this->first_tag_close;
		}

		// Render the "previous" link
		if  (($this->curPage - $this->num_links) >= 0)
		{
			$i = $this->cur_item - $this->per_page;
			$curParams = ($i == 0) ? $this->base_params : array_merge($this->base_params, array('pagestart' => $i));
			$output .= $this->prev_tag_open.'<a href="'. $modx->makeUrl($curId, '', $curParams) .'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
		}

		// Write the digit links
		for ($loop = $start; $loop <= $end; $loop++)
		{
			$i = ($loop - 1) * $this->per_page + 1;
					
			if ($i >= 0)
			{
				if ($this->curPage == $loop)
				{
					$output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
				}
				else
				{
					$curParams = ($i == 0) ? $this->base_params : array_merge($this->base_params, array('pagestart' => $i));
					$output .= $this->num_tag_open . '<a href="'. $modx->makeUrl($curId, '', $curParams) . '">' . $loop.'</a>'.$this->num_tag_close;
				}
			}
		}

		// Render the "next" link
		if ($this->curPage < $num_pages)
		{
			$curParams = array_merge($this->base_params, array('pagestart' => ($this->curPage * $this->per_page + 1)));
			$output .= $this->next_tag_open . '<a href="'. $modx->makeUrl($curId, '', $curParams) . '">' .$this->next_link.'</a>'.$this->next_tag_close;
		}

		// Render the "Last" link
		if (($this->curPage + $this->num_links) < $num_pages)
		{
			$i = (($num_pages * $this->per_page) - $this->per_page + 1);
			$curParams = array_merge($this->base_params, array('pagestart' => $i));
			$output .= $this->last_tag_open . '<a href="'. $modx->makeUrl($curId, '', $curParams) . '">' .  $this->last_link.'</a>'.$this->last_tag_close;
		}

		// Kill double slashes.  Note: Sometimes we can end up with a double slash
		// in the penultimate link so we'll kill all double slashes.
		$output = preg_replace("#([^:])//+#", "\\1/", $output);

		// Add the wrapper HTML if exists
		$output = $this->full_tag_open.$output.$this->full_tag_close;
		
		return $output;		
  }
}
// END Pagination Class
?>
