<?php
 namespace MailPoetVendor\Egulias\EmailValidator\Exception; if (!defined('ABSPATH')) exit; class ExpectingAT extends InvalidEmail { const CODE = 202; const REASON = "Expecting AT '@' "; } 