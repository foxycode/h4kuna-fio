<?php

namespace h4kuna\Fio;

use h4kuna\Fio\Request\Pay;

class FioPay extends Fio
{

	/** @var string[] */
	private static $langs = ['en', 'cs', 'sk'];

	/** @var string */
	private $uploadExtension;

	/** @var string */
	private $language = 'cs';

	/** @var Pay\XMLResponse */
	private $response;

	/** @var Pay\PaymentFactory */
	private $paymentFatory;

	/** @var Pay\XMLFile */
	private $xmlFile;

	public function __construct(Request\IQueue $queue, Account\AccountCollection $accountCollection, Pay\PaymentFactory $paymentFactory, Pay\XMLFile $xmlFile)
	{
		parent::__construct($queue, $accountCollection);
		$this->paymentFatory = $paymentFactory;
		$this->xmlFile = $xmlFile;
	}

	/** @return Pay\Payment\Euro */
	public function createEuro($amount, $accountTo, $bic, $name, $country)
	{
		return $this->paymentFatory->createEuro($amount, $accountTo, $bic, $name, $country);
	}

	/** @return Pay\Payment\National */
	public function createNational($amount, $accountTo, $bankCode = NULL)
	{
		return $this->paymentFatory->createNational($amount, $accountTo, $bankCode);
	}

	/** @return Pay\Payment\International */
	public function createInternational($amount, $accountTo, $bic, $name, $street, $city, $country, $info)
	{
		return $this->paymentFatory->createInternational($amount, $accountTo, $bic, $name, $street, $city, $country, $info);
	}

	/** @return Pay\IResponse */
	public function getUploadResponse()
	{
		return $this->response;
	}

	/**
	 * @param Pay\Payment\Property $property
	 * @return self
	 */
	public function addPayment(Pay\Payment\Property $property)
	{
		$this->xmlFile->setData($property);
		return $this;
	}

	/**
	 * @param string|Pay\Payment\Property $filename
	 * @return Response\Pay\IResponse
	 * @throws InvalidArgumentException
	 */
	public function send($filename = NULL)
	{
		if ($filename instanceof Pay\Payment\Property) {
			$this->xmlFile->setData($filename);
		}

		if ($this->xmlFile->isReady()) {
			$this->setUploadExtenstion('xml');
			$filename = $this->xmlFile->getPathname();
		} elseif (is_file($filename)) {
			$this->setUploadExtenstion(pathinfo($filename, PATHINFO_EXTENSION));
		} else {
			throw new InvalidArgumentException('Is supported only filepath or Property object.');
		}

		$token = $this->getActive()->getToken();
		$post = [
			'type' => $this->uploadExtension,
			'token' => $token,
			'lng' => $this->language,
		];

		return $this->response = $this->queue->upload($this->getUrl(), $token, $post, $filename);
	}

	/**
	 * Response language.
	 * @param string $lang
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function setLanguage($lang)
	{
		$lang = strtolower($lang);
		if (!in_array($lang, self::$langs)) {
			throw new InvalidArgumentException($lang . ' avaible are ' . implode(', ', self::$langs));
		}
		$this->language = $lang;
		return $this;
	}

	/** @return string */
	private function getUrl()
	{
		return self::REST_URL . 'import/';
	}

	/**
	 * Set upload file extension.
	 * @param string $extension
	 * @return self
	 * @throws InvalidArgumentException
	 */
	private function setUploadExtenstion($extension)
	{
		$extension = strtolower($extension);
		static $extensions = ['xml', 'abo'];
		if (!in_array($extension, $extensions)) {
			throw new InvalidArgumentException('Unsupported file upload format: ' . $extension . ' avaible are ' . implode(', ', $extensions));
		}
		$this->uploadExtension = $extension;
		return $this;
	}

}
