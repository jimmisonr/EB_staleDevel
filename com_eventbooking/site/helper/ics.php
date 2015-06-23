<?php
/**
 * @version        	1.7.3
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
class EventbookingHelperIcs
{
	/**
	 *
	 * The name of the event
	 * @var string
	 */
	protected $name;

	/**
	 * The event start date
	 * @var DateTime
	 */
	protected $eventDate;

	/**
	 * The event end date
	 * @var DateTime
	 */
	protected $eventEndDate;

	/**
	 * The event location
	 * @var string
	 */
	protected $location;

	/**
	 * The description of event
	 * @var string
	 */
	protected $description;

	/**
	 * The sender's name
	 * @var string
	 */
	protected $fromName;

	/**
	 * Sender's email
	 * @var string
	 */
	protected $fromEmail;

	/***
	 * Constructor function
	 */
	public function __construct()
	{
		$this->_uid = uniqid();
	}

	/**
	 * Get UID
	 *
	 * @return string
	 */
	public function getUID()
	{
		return $this->_uid;
	}

	/**
	 * Set the start datetime
	 *
	 * @param string $start
	 *
	 * @return \EventbookingHelperIcs
	 */
	public function setStart($start)
	{
		$this->eventDate = JFactory::getDate($start, new DateTimeZone(JFactory::getConfig()->get('offset')));

		return $this;
	}

	/**
	 * Set the end datetime
	 *
	 * @param string $end
	 *
	 * @return \EventbookingHelperIcs
	 */
	public function setEnd($end)
	{
		$this->eventEndDate = JFactory::getDate($end, new DateTimeZone(JFactory::getConfig()->get('offset')));;

		return $this;
	}

	/**
	 *
	 * Set event organizer
	 *
	 * @param string $email
	 * @param string $name
	 *
	 * @return \EventbookingHelperIcs
	 *
	 */
	public function setOrganizer($email, $name = null)
	{
		if (null === $name)
		{
			$name = $email;
		}

		$this->fromEmail = $email;
		$this->fromName  = $name;

		return $this;
	}

	/**
	 * Set the name of the event
	 *
	 * @param string $name
	 *
	 * @return \EventbookingHelperIcs
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 *
	 * Set the invite body content
	 *
	 * @param string $body
	 *
	 * @return \EventbookingHelperIcs
	 */
	public function setDescription($body)
	{
		$converter = new EventbookingHelperHtml2text($body);
		$converter->setBaseUrl(JUri::base());
		$this->description = $converter->getText();

		return $this;
	}

	/**
	 *
	 * Set the location where the event will take place
	 *
	 * @param string $location
	 *
	 * @return \EventbookingHelperIcs
	 */
	public function setLocation($location)
	{
		$this->location = $location;

		return $this;
	}

	/**
	 *
	 * Get the location where the event will be held
	 *
	 * @return type
	 */
	public function getLocation()
	{
		return preg_replace('/([\,;])/', '\\\$1', $this->location);
	}

	/**
	 *
	 * Get the event name
	 * @return string
	 */
	public function getName()
	{
		return preg_replace('/([\,;])/', '\\\$1', $this->name);
	}

	/**
	 * Get the current body content
	 * @return string
	 */
	public function getDescription()
	{
		return preg_replace('/([\,;])/', '\\\$1', $this->description);
	}

	/**
	 * Get the name of the invite sender
	 * @return string
	 */
	public function getFromName()
	{
		return $this->fromName;
	}

	/**
	 * Get the email where the email will be sent from
	 * @return string
	 */
	public function getFromEmail()
	{
		return $this->fromEmail;
	}

	/**
	 * Get the start time set for the even
	 * @return string
	 */
	public function getStart()
	{
		return $this->eventDate->format("Ymd\THis\Z");
	}

	/**
	 * Get the end time set for the event
	 * @return string
	 */
	public function getEnd()
	{
		return $this->eventEndDate->format("Ymd\THis\Z");
	}

	/**
	 * Get created date of the ics file
	 *
	 * @return string
	 */
	public function getCreatedDate()
	{
		$createdDate = JFactory::getDate('Now', new DateTimeZone(JFactory::getConfig()->get('offset')));

		return $createdDate->format("Ymd\THis\Z");
	}

	/**
	 *
	 * Save the invite to a file
	 *
	 * @param string $path
	 * @param string $name
	 *
	 * @return string
	 *
	 */
	public function save($path, $name = null)
	{
		jimport('joomla.filesystem.folder');
		if ($name == null)
		{
			$name = $this->getUID() . '.ics';
		}
		// create path if it doesn't exist
		if (!is_dir($path))
		{
			JFolder::create($path);
		}

		$handler = fopen($path . $name, 'w+');
		$data = $this->generate();
		fwrite($handler, $data);
		fclose($handler);

		return $path . $name;
	}

	/**
	 * Method to download ics file
	 */
	public function download()
	{
		while (@ob_end_clean()) ;
		$data = $this->generate();
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"calendar.ics\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . strlen($data));
		print $data;
	}
	/**
	 *
	 * The function generates the actual content of the ICS
	 * file and returns it.
	 *
	 * @return string|bool
	 */
	protected function generate()
	{

		$content = "BEGIN:VCALENDAR\n";
		$content .= "VERSION:2.0\n";
		$content .= "CALSCALE:GREGORIAN\n";
		$content .= "METHOD:REQUEST\n";
		$content .= "BEGIN:VEVENT\n";
		$content .= "UID:{$this->getUID()}\n";
		$content .= "DTSTART:{$this->getStart()}\n";
		$content .= "DTEND:{$this->getEnd()}\n";
		$content .= "DTSTAMP:{$this->getStart()}\n";
		$content .= "ORGANIZER;CN={$this->getFromName()}:mailto:{$this->getFromEmail()}\n";
		$content .= "CREATED:{$this->getCreatedDate()}\n";
		$content .= "DESCRIPTION:{$this->getDescription()}\n";
		$content .= "LAST-MODIFIED:{$this->getStart()}\n";
		$content .= "LOCATION:{$this->getLocation()}\n";
		$content .= "SUMMARY:{$this->getName()}\n";
		$content .= "SEQUENCE:0\n";		
		$content .= "TRANSP:OPAQUE\n";
		$content .= "END:VEVENT\n";
		$content .= "END:VCALENDAR";

		return $content;
	}
}