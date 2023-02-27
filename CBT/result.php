<link rel="stylesheet" type="text/css" href="css/mycss.css">
<style>
  #customers {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
  }

  #customers td,
  #customers th {
    border: 1px solid #ddd;
    padding: 8px;
  }

  #customers tr:nth-child(even) {
    background-color: #f2f2f2;
  }

  #customers tr:hover {
    background-color: #ddd;
  }

  #customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: red;
    color: white;
  }




  #test p {
    opacity: 0;
    font-size: 21px;
    margin-top: 25px;
    text-align: center;

    -webkit-transition: opacity 2s ease-in;
    -moz-transition: opacity 2s ease-in;
    -ms-transition: opacity 2s ease-in;
    -o-transition: opacity 2s ease-in;
    transition: opacity 2s ease-in;
  }

  #test p.load {
    opacity: 1;
  }
</style>
<style>
  input[type=text],
  select {
    width: 60%;
    padding: 12px 20px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
  }

  input[type=submit] {
    width: 90%;
    background-color: red;
    color: white;
    padding: 6px 11px;
    margin: 8px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }

  input[type=submit]:hover {
    background-color: #1589FF;
  }

  .button {
    display: inline-block;
    border-radius: 25px;
    background-color: darkblue;
    border: none;
    color: #FFFFFF;
    text-align: center;
    font-size: 14px;
    padding: 10px;
    width: 150px;
    transition: all 0.5s;
    cursor: pointer;
    margin: 5px;
  }

  .excel {
    height: 40px;
    width: 7%;
    background-color: powderblue;
  }
</style>
<script>
  function exportTableToExcel(tableID, filename = '') {
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

    // Specify file name
    filename = filename ? filename + '.xls' : 'excel_data.xls';

    // Create download link element
    downloadLink = document.createElement("a");

    document.body.appendChild(downloadLink);

    if (navigator.msSaveOrOpenBlob) {
      var blob = new Blob(['\ufeff', tableHTML], {
        type: dataType
      });
      navigator.msSaveOrOpenBlob(blob, filename);
    } else {
      // Create a link to the file
      downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

      // Setting the file name
      downloadLink.download = filename;

      //triggering the function
      downloadLink.click();
    }
  }
</script>

<div class="app-main__outer">
  <div class="app-main__inner">
    <div class="app-page-title">
      <div class="page-title-wrapper">
        <div class="page-title-heading">
          <div>RESULT</div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="main-card mb-3 card">
        <div class="card-header">Export
        </div>
        <div class="excel"> <input type="submit" onclick="exportTableToExcel('customers','filename')" name="excel" value="Excel" /></div>
        <div class="table-responsive">
          <table class="align-middle mb-0 table table-borderless table-striped table-hover" id="tableList">
            <form method="post">
              <tr>
                <td><strong>Select Exam </strong></td>
                <td colspan="4"><select name="examid" id="examid" style="margin-bottom: 20px; width: 630px;">
                    <?php
                    require_once('connect.php');
                    $rs = "Select * from exam_tbl order by ex_title";
                    echo "<option value='' selected>----Select Exam ----</option>";
                    $r = mysqli_query($con, $rs) or die(mysqli_error($con));
                    while ($row = mysqli_fetch_array($r)) {
                      /*if($row[0]==$testid)
{
echo "<option value='$row[0]' selected>$row[2]</option>";
}
else
{*/
                      echo "<option value='$row[0]'>$row[2]</option>";
                      //}
                    }
                    ?>
                  </select> <br></td>
                <td> <input type="submit" name="Submit" value="Show Results" /> </td>
              </tr>
            </form>
            <thead>
              <table id="customers">

                <tr>
                  <th>ExamNo</th>
                  <th>Fullname</th>
                  <th>Exam Name</th>
                  <th>Email</th>
                  <th>Score</th>
                  <th>Questions </th>
                  <th>Ratings</th>
                </tr>

                <?php
                if (isset($_POST['examid'])) {
                  $examinee_id = 76;
                  $exam_id = $_POST['examid'];
                  $totalscore = 0;
                  $sel2 = mysqli_query($con, "select * from exam_tbl where ex_id='$exam_id'") or die(mysqli_error($con));
                  while ($row4 = mysqli_fetch_array($sel2)) {
                    $total_question = $row4['ex_questlimit_display'];
                    $ex_title = $row4['ex_title'];
                  }

                  $sel = mysqli_query($con, "select examinee_tbl.exmne_fullname ,examinee_tbl.exam_no ,examinee_tbl.exmne_email, answer.exam_detail from answer inner join examinee_tbl on examinee_tbl.exmne_id= answer.candidate_id where  exam_id='$exam_id' ") or die(mysqli_error($con)); //candidate_id='$examinee_id'  and
                  $cont = mysqli_num_rows($sel);
                  while ($row = mysqli_fetch_array($sel)) {
                    $exam_detail = $row['exam_detail'];
                    $name = $row['exmne_fullname'];
                    $exam_no = $row['exam_no'];
                    $email = $row['exmne_email'];
                    $exam_detail1 = explode(",", $exam_detail);
                    $totalscore  = 0;
                    foreach ($exam_detail1 as $value) {
                      //echo $value."<br>";
                      $exam = explode(":", $value);
                      //echo $exam[1]."<br>";
                      $question_id = $exam[0];
                      $answer = $exam[1];

                      //$check=mysqli_query($con,"select sum(AllCount) AS Total_Count from((select count(eqt_id) AS AllCount from exam_question_tbl where eqt_id='$question_id' and exam_answer <>'$answer'))t
                      //") or die(mysqli_error($con));
                     // $check = mysqli_query($con, "select count(eqt_id) from exam_question_tbl where eqt_id='$question_id' and exam_answer ='$answer'") or die(mysqli_error($con));

                      //while ($row1 = mysqli_fetch_array($check)) {
                      //  $score = $row1['count(eqt_id)'];
                        //echo $score."<br>";
                       // if ($score > 0) {
                       //   $totalscore += $score;
                       // }
                     // }
                     
                     // To mark exam use the code below and uncomment break at the end of while loop below
                     //$check1 = mysqli_query($con, "select eqt_id, exam_answer from exam_question_tbl where eqt_id='$question_id'") or die(mysqli_error($con));
                    // $num_check1 = mysqli_num_rows($check1);
                   //  $row1 = mysqli_fetch_array($check1);                
                   // echo $row1['exam_answer']. "= ". $answer. "<br>";

                     $check = mysqli_query($con, "select eqt_id, exam_answer from exam_question_tbl where eqt_id='$question_id' and exam_answer ='$answer'") or die(mysqli_error($con));
                     $num_check = mysqli_num_rows($check);

                     if ($num_check){
                         $totalscore +=1;
                        }
                      
                    }

                    //$score1=mysqli_num_rows($check);
                    //$score_value = $totalscore . "/" . $total_question;
                    $per = ($totalscore /$total_question ) * 100;

                    //echo $per."%<br>";
                ?>


                    <tr>

                      <td><?php echo $exam_no; ?></td>
                      <td><?php echo $name; ?></td>
                      <td><?php echo $ex_title; ?></td>
                      <td><?php echo $email; ?></td>
                      <td><?php echo $totalscore; ?></td>
                      <td><?php echo $total_question; ?></td>
                      <td><?php echo $per . "%"; ?></td>
                    </tr>
                <?php 
                //break;
              }
                } ?>

              </table>


              </tbody>
          </table>
        </div>
      </div>
    </div>


  </div>