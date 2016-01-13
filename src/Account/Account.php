<?php

namespace h4kuna\Fio\Account;

/**
 * @author Milan Matějček
 */
class Account
{
	/** @var Bank */
	private $account;

	/** @var string */
	private $token;

	public function __construct($account, $token = NULL)
	{
		$this->account = new Bank($account);
		$this->token = $token;
	}

	/** @return string */
	public function getAccount()
	{
		return $this->account->getAccount();
	}

	/** @return string */
	public function getBankCode()
	{
		return $this->account->getBankCode();
	}

	/** @return string */
	public function getToken()
	{
		return $this->token;
	}

}