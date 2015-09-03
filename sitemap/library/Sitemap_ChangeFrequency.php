<?php

namespace Craft;

abstract class SitemapChangeFrequency extends BaseEnum
{
	const Always  = 'always';
	const Hourly  = 'hourly';
	const Daily   = 'daily';
	const Weekly  = 'weekly';
	const Monthly = 'monthly';
	const Yearly  = 'yearly';
	const Never   = 'never';
}
