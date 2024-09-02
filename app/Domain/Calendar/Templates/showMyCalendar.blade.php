@extends($layout)

@section('content')

<?php
if (!session()->exists("usersettings.submenuToggle.myCalendarView")) {
    session(["usersettings.submenuToggle.myCalendarView" => "dayGridMonth"]);
}
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('headline.calendar'); ?></h5>
        <h1><?php echo $tpl->__('headline.my_calendar'); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<?php
    echo $tpl->displayNotification();
?>

<div class="maincontent">

    <div class="row">
        <div class="col-md-2">
            <div class="maincontentinner">
                <h5 class="subtitle pb-m">Calendars</h5>

                <ul class="simpleList">
                    <li><span class="indicatorCircle" style="background:var(--accent1)"></span>Events</li>
                    <li><span class="indicatorCircle" style="background:var(--accent2)"></span>Projects & Tasks</li>


                <?php foreach ($tpl->get('externalCalendars') as $calendars) { ?>
                    <li>
                        <div class="inlineDropDownContainer" style="float:right;">
                            <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown editHeadline" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                            </a>

                            <ul class="dropdown-menu">
                                <li>
                                    <a href="#/calendar/editExternal/<?=$calendars['id']?>"><i class="fa-solid fa-pen-to-square"></i> <?=$tpl->__('links.edit_calendar')?></a>
                                </li>
                                <li><a href="#/calendar/delExternalCalendar/<?=$calendars['id']?>" class="delete"><i class="fa fa-trash"></i> <?=$tpl->__('links.delete_external_calendar')?></a></li>
                            </ul>
                        </div>
                        <span class="indicatorCircle" style="background:<?=$calendars['colorClass'] ?>"></span><?=$calendars['name'] ?>

                    </li>
                <?php } ?>

                </ul>
                <hr />
                <a href="#/calendar/importGCal"><i class="fa-regular fa-calendar-plus"></i> Import Calendar</a>
            </div>
        </div>
        <div class="col-md-10">
            <div class="maincontentinner">
                <div class="row">
                    <div class="col-md-4">
                        <a href="#/calendar/addEvent" class="btn btn-primary formModal"><i class='fa fa-plus'></i> <?=$tpl->__('buttons.add_event')?></a>
                    </div>
                    <div class="col-md-4">
                        <div class="fc-center center" id="calendarTitle" style="padding-top:5px;">
                            <h2>..</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="#/calendar/export" class="btn btn-default right">Export</a>
                        <button class="fc-next-button btn btn-default right" type="button" style="margin-right:5px;">
                            <span class="fc-icon fc-icon-chevron-right"></span>
                        </button>
                        <button class="fc-prev-button btn btn-default right" type="button" style="margin-right:5px;">
                            <span class="fc-icon fc-icon-chevron-left"></span>
                        </button>

                        <button class="fc-today-button btn btn-default right" style="margin-right:5px;">today</button>


                        <select id="my-select" style="margin-right:5px;" class="right">
                            <option class="fc-timeGridDay-button fc-button fc-state-default fc-corner-right" value="timeGridDay" <?=session("usersettings.submenuToggle.myCalendarView") == 'timeGridDay' ? "selected" : '' ?>>Day</option>
                            <option class="fc-timeGridWeek-button fc-button fc-state-default fc-corner-right" value="timeGridWeek" <?=session("usersettings.submenuToggle.myCalendarView") == 'timeGridWeek' ? "selected" : '' ?>>Week</option>
                            <option class="fc-dayGridMonth-button fc-button fc-state-default fc-corner-right" value="dayGridMonth" <?=session("usersettings.submenuToggle.myCalendarView") == 'dayGridMonth' ? "selected" : '' ?>>Month</option>
                            <option class="fc-multiMonthYear-button fc-button fc-state-default fc-corner-right" value="multiMonthYear" <?=session("usersettings.submenuToggle.myCalendarView") == 'multiMonthYear' ? "selected" : '' ?>>Year</option>
                        </select>
                    </div>
                </div>
                <div id="calendar"></div>
            </div>
        </div>
    </div>


</div>


<script type='text/javascript'>

    <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>


    jQuery(document).ready(function() {

        //leantime.calendarController.initCalendar(events);
        leantime.calendarController.initExportModal();

    });
    var eventSources = [];

    var events = {events: [
        <?php foreach ($tpl->get('calendar') as $calendar) : ?>
        {
            title: <?php echo json_encode($calendar['title']); ?>,

            start: new Date(<?php echo format($calendar['dateFrom'])->jsTimestamp() ?>),
            <?php if (isset($calendar['dateTo'])) : ?>
            end: new Date(<?php echo format($calendar['dateTo'])->jsTimestamp() ?>),
            <?php endif; ?>
            <?php if ((isset($calendar['allDay']) && $calendar['allDay'] === true)) : ?>
            allDay: true,
            <?php else : ?>
            allDay: false,
            <?php endif; ?>
            enitityId: <?php echo $calendar['id'] ?>,
            <?php if (isset($calendar['eventType']) && $calendar['eventType'] == 'calendar') : ?>
            url: '<?=CURRENT_URL ?>#/calendar/editEvent/<?php echo $calendar['id'] ?>',
            backgroundColor: '<?= $calendar['backgroundColor'] ?? "var(--accent2)" ?>',
            borderColor: '<?= $calendar['borderColor'] ?? "var(--accent2)" ?>',
            enitityType: "event",
            <?php else : ?>
            url: '<?=CURRENT_URL ?>#/tickets/showTicket/<?php echo $calendar['id'] ?>?projectId=<?php echo $calendar['projectId'] ?>',
            backgroundColor: '<?= $calendar['backgroundColor'] ?? "var(--accent2)" ?>',
            borderColor: '<?= $calendar['borderColor'] ?? "var(--accent2)" ?>',
            enitityType: "ticket",
            <?php endif; ?>
        },
        <?php endforeach; ?>
    ]};

    eventSources.push(events);

    <?php
    $externalCalendars = $tpl->get("externalCalendars");

    foreach ($externalCalendars as $externalCalendar) { ?>
        eventSources.push(
            {
                url: '<?=BASE_URL ?>/calendar/externalCal/<?=$externalCalendar['id'] ?>',
                format: 'ics',
                color: '<?=$externalCalendar['colorClass'] ?>',
                editable: false,
            }
        );

    <?php } ?>


    document.addEventListener('DOMContentLoaded', function() {
        leantime.calendarController.initShowMyCalendar(
            document.getElementById('calendar'),
            eventSources,
            '<?=session("usersettings.submenuToggle.myCalendarView") ?>',
        );
    });

    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
