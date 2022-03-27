$('.daterange').daterangepicker({
    ranges: {
      Today: [moment(), moment()],
      Yesterday: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Last 7 Days': [moment().subtract(6, 'days'), moment()],
      'Last 30 Days': [moment().subtract(29, 'days'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    startDate: moment().subtract(29, 'days'),
    endDate: moment()
  }, function (start, end) {
    if(document.getElementById("event_id").value == '' || document.getElementById("org_id").value=='' ) {
         alert('Select an Organization and an Event');
    } else {
   
    let event = "event_id=" + document.getElementById("event_id").value ;
    let org = "org_id=" + document.getElementById("org_id").value;
   location.replace('/attendance/report/all?'+ event +'&'+ org + '&start_date='+start.format()+'&end_date='+end.format());
    }
  })