<?php
 namespace MailPoetVendor\Egulias\EmailValidator\Exception; if (!defined('ABSPATH')) exit; class CRNoLF extends InvalidEmail { const CODE = 150; const REASON = "Missing LF after CR"; } 