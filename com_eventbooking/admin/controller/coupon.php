<?php
/**
 * @version            2.5.1
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Membership Pro controller
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class EventbookingControllerCoupon extends EventbookingController
{

	/**
	 * Method to import coupon codes from a csv file
	 */
	public function import()
	{
		$model         = $this->getModel('Coupon');
		$numberCoupons = $model->import($this->input);
		if ($numberCoupons === false)
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=coupon&layout=import', JText::_('EB_NO_COUPONS_IMPORTED'));
		}
		else
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=coupons',
				JText::sprintf('EB_NUMBER_COUPONS_IMPORTED', $numberCoupons));
		}
	}

	/**
	 * Export Coupons into a CSV file
	 */
	public function export()
	{
		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true);
		$nullDate  = $db->getNullDate();
		$eventId    = $this->input->getInt('filter_event_id');
		$published = $this->input->get('filter_state', '');

		switch ($published)
		{
			case "P":
				$published = 1;
				break;
			case "U":
				$published = 0;
				break;
		}

		$query->select('a.*, b.title')
			->from('#__eb_coupons AS a')
			->leftJoin('#__eb_events AS b ON a.event_id = b.id');

		if ($eventId > 0)
		{
			$query->where('a.event_id = ' . $eventId);
		}

		if ($published != '')
		{
			$query->where(' a.published=' . $published);
		}
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if (count($rows))
		{

			$results_arr   = array();
			$results_arr[] = JText::_('event');
			$results_arr[] = JText::_('code');
			$results_arr[] = JText::_('discount');
			$results_arr[] = JText::_('coupon_type');
			$results_arr[] = JText::_('times');
			$results_arr[] = JText::_('used');
			$results_arr[] = JText::_('valid_from');
			$results_arr[] = JText::_('valid_to');
			$results_arr[] = JText::_('published');
			$csv_output    = "\"" . implode("\",\"", $results_arr) . "\"";

			foreach ($rows as $r)
			{
				$results_arr   = array();
				if ($r->event_id == -1)
				{
					$results_arr[] = $r->title;
				}
				else 
				{
					$query->clear();
					$query->select('a.id')
						->from('#__eb_events AS a')
						->leftJoin('#__eb_coupon_events AS b ON a.id=b.event_id')
						->where('b.coupon_id='.(int)$r->id);
					$db->setQuery($query);
					$results_arr[] = implode(',',$db->loadColumn());
				}
				$results_arr[] = $r->code;
				$results_arr[] = round($r->discount, 2);
				$results_arr[] = $r->coupon_type;
				$results_arr[] = $r->times;
				$results_arr[] = $r->used;
				if ($r->valid_from != $nullDate && $r->valid_from)
				{
					$results_arr[] = JHtml::_('date', $r->valid_from, 'Y-m-d', null);
				}
				else
				{
					$results_arr[] = '';
				}

				if ($r->valid_to != $nullDate && $r->valid_to)
				{
					$results_arr[] = JHtml::_('date', $r->valid_to, 'Y-m-d', null);
				}
				else
				{
					$results_arr[] = '';
				}

				$results_arr[] = $r->published;
				$csv_output .= "\n\"" . implode("\",\"", $results_arr) . "\"";
			}
			$csv_output .= "\n";
			if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
			{
				$UserBrowser = "Opera";
			}
			elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
			{
				$UserBrowser = "IE";
			}
			else
			{
				$UserBrowser = '';
			}
			$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
			$filename  = "coupon_list";
			@ob_end_clean();
			ob_start();
			header('Content-Type: ' . $mime_type);
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			if ($UserBrowser == 'IE')
			{
				header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}
			else
			{
				header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
				header('Pragma: no-cache');
			}
			print $csv_output;
			exit();
		}
	}

	/**
	 * Batch coupon generation
	 */
	public function batch()
	{
		$model         = $this->getModel('Coupon');
		$model->batch($this->input);
		$this->setRedirect('index.php?option=com_eventbooking&view=coupons', JText::_('EB_COUPONS_SUCCESSFULLY_GENERATED'));
	}
}