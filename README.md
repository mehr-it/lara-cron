# LaraCron - dynamic user cron schedules
This package helps to implement user manageable, distributed cron jobs.
Cron jobs are stored in a database table and are
executed whenever given cron expression matches.

Unlike other cron implementations, this package always uses queuing for dispatching cron
jobs. The cron jobs are sent to the queue regularly, just a few minutes before they are
due - but with a delay, so the jobs won't get received until there desired execution time.

This approach has several advantages:
* Scheduler down time of a view minutes does not cause miss of cron jobs
* Cron jobs can be caught up if missed
* Cron job execution can be distributed over many machines
* You may execute the cron scheduler on multiple machines without to care for duplicate
  cron job dispatches, because duplicate prevention is already built-in

## Usage

Creating cron schedules is very easy:

	$cron = new CronExpression('*/15 * * * *', 'Europe/Berlin');
	
	Cron::schedule($cron, $job);
	
	Cron::schedule($cron, $job, 'myJob', 'jobGroup');
	
To update an existing schedule, simply call `schedule()` with
same key parameter again.

To delete a cron schedule, simply call the `delete()` method:

	Cron::delete('myJob');
	
You can also list all cron schedules, optionally filtered
by group name:

	Cron::describeAll();
	
	Cron::describeAll('myGroup'); 	

## Invoking the scheduler

The cron scheduler dispatches all upcoming cron jobs to
queue. By default it is invoked by Laravel's Scheduler
every five minutes and dispatches cron jobs due within 
next 10 minutes.

You can disable this behavior by setting:

	// config/cron.php

	'dispatch_schedule' => false,

Then you have to invoke `artisan cron:dispatch 600` 
manually. The `600` specifies the period (in seconds)
for which to dispatch the cron jobs for. This value 
should always be larger than the timespan between these
dispatcher calls.

## Catchup of missed cron jobs

Systems fail. Even cron schedulers. Or systems running the
scheduler may be down for some time. Therefore you may
specify a catchup timeout for your cron jobs as last 
parameter.

Cron::schedule($cron, $job, 'my-job', null, true, 300);

The dispatcher will do some catching up for jobs that were
due within that period but have not been scheduled yet.

## DST handling

DST (daylight saving time) and cron expressions can be
very tricky and different cron implementations may 
behave differently.

The problem which has to be solved are the skipping of 
one clock hour at DST start and repeating a clock hour on
DST end. Without any special handling, cron jobs might
not run at all or being invoked double.

Following describes how this package handles DST clock
changes.

### DST start (turning clock from 2:00 to 3:00)
On DST start, cron jobs scheduled between 2:00 and 2:59
simply start between 3:00 and 3:59.

This way no cron is missed, but you should beware that
some jobs could overlap others which they usually dont.

### DST end (turning clock back from 3:00 to 2:00)
On DST end, the handling depends on the hour expression
in the cron field. If a wildcard or a range matching
the period between 2:00 and 3:00 is given, the cron
jobs are run twice. For lists, increments and single
values, cron jobs are only run once.

Following table shows which expressions would cause
repeating and which not:


| repeated			  | not repeated       |
|---------------------|--------------------|
| `0 * * * *`	      | `0 2 * * *`          |
| `30 * * * *`	      | `30 2 * * *`         |
| `0 2-3 * * *`		  | `0 2,3 * * *`        |
| `0 1-4 * * *`		  | `0 0-2 * * *`        |
|           		  | `*/5 2 * * *`        |
|           		  | `5 2-12/2 * * *`     |
