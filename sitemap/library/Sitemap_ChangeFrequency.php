<?php

namespace Craft;

abstract class Sitemap_ChangeFrequency extends BaseEnum
{
	const Always  = 'always';
	const Hourly  = 'hourly';
	const Daily   = 'daily';
	const Weekly  = 'weekly';
	const Monthly = 'monthly';
	const Yearly  = 'yearly';
	const Never   = 'never';
}
