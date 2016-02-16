<?php
include "settings.php";

$db_conn = new mysqli( DB_HOST ,DB_USER , DB_PASSWORD , DB_NAME ) or die("watafaq");
if ( $db_conn->connect_error )
{
  die ("Cannot connect to Database");
}

if(isset($_GET['logged']))
{
  $logged = $_GET['logged'];
}
else{
  $logged = 0;
}

if($logged == 0){
  $str_id = $_GET['username'];
  $psswrd = $_GET['password'];

  $query = "select * from str_lgn_dtls where store_id = '$str_id' and password_hash = '$psswrd'";
  $result = $db_conn->query($query);
  $num_of_rows = $result->num_rows;
  if ( $num_of_rows == 1 )
  {
    while ( $row = $result->fetch_assoc() ){
      $store_name = $row['name'];
      $logged = 1;    
    }
  }
  else
  {
    echo "Incorrect Details. Please try Again <br/>";
    include "index.php";
  }

}

if($logged == 1){
  $store_id = $_GET['username'];
  $html = file_get_contents("template.html");
    if ($html === FALSE)
      die("Error accessing HTML template.");

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <title>Walk N Talk | Seller</title>
    <style type="text/css">
      body {
        width: 960px;
        margin: 25px auto;
        font-family: Arial, sans-serif;
        font-size: 14px;
      }
      h2 {
        margin: 0;
        font-size: 24px;
      }
      h3 {
        margin: 0 0 10px;
        font-size: 18px;
      }
      label {
        display: inline-block;
        line-height: 25px;
      }
      select {
        width: 150px;
        padding: 3px 0;
      }
      input[type="button"] {
        padding: 3px 9px;
      }
      input[type="text"] {
        width: 205px;
        padding: 3px 2px;
        margin-right: 12px;
      }
      tr {
        height: 40px;
      }
      .fieldset {
        max-height: 500px;
        padding: 0 10px 5px 10px;
        border: 1px solid #aaa;
        margin-top: 5px;
        overflow: scroll;
      }
      #form label {
        margin-right: 4px;
      }
      #savebutton {
        float: right;
        width: 100px;
      }
      .option {
        padding: 20px 0;
        border-bottom: 1px solid #aaa;
        overflow: hidden;
      }
      .option.logic {
        padding-bottom: 0;
        border-bottom: none;
      }
      .option label {
        margin-right: 5px;
      }
      .option .field + .field,
      #addbutton {
        margin-top: 10px;
      }
      .option.mspid .field {
        float: right;
      }
      .option.mspid .field input[type="text"] {
        width: 675px;
        margin-right: 0;
      }
      .option.logic .field input[type="text"] {
        width: 32px;
        margin-right: 5px;
      }
      .option .field input[type="checkbox"] {
        margin: 10px 5px 10px 0;
      }
      .option .help {
        font-size: 12px;
        line-height: 25px;
        color: #777;
      }
    </style>
  </head>
  <body>
    <h2>Hello <?php echo $store_name;?></h2>
    <form id="form" method="post">
      <input id="action" type="hidden" name="action" value=""/>
      <input id="logged" type="hidden" name="logged" value="1"/>
      <input id="username" type="hidden" name="username" value='<?php echo $str_id; ?>'/>
      <div class="option subcategory">
        <label for="subcategory">Category:</label>
        <select id="subcategory" name="subcategory">
          <option value="null">&ndash; Select &ndash;</option>
          <?php
          $category_query = "SELECT distinct(category) FROM categories";
          $category_result = $db_conn->query($category_query);
          $subcategories = array();
          while($category_row = $category_result->fetch_assoc()){
            $subcategories[] = $category_row['category'];
          }
            foreach ($subcategories as $subcategory) {
              echo "<option value='$subcategory'" . ($_POST["subcategory"] === $subcategory ? " selected" : "") . ">$subcategory</option>";
            }
          ?>
        </select>
        <?php
        if(!empty($_POST['subcategory']) && $_POST["subcategory"] !== "null"){
          echo '<input id="savebutton" type="button" value="Save"/>';
        }
        ?>
      </div>
      <?php
        if(!empty($_POST['subcategory']) && $_POST["subcategory"] !== "null"){
          $category = $db_conn->escape_string($_POST["subcategory"]);
          if ($_POST["action"] == "save") {
            $store_id = $db_conn->escape_string($_POST["username"]);
            $links = $_POST['links'];

            $select_query = "SELECT * FROM categories WHERE category='".$category."'";
            $select_result = $db_conn->query($select_query);
            if(!$select_result){
              die("Error Fetching Data");
            }
            $subcategory_array = array();
            while($select_row = $select_result->fetch_assoc()){
              $subcategory_array[$select_row['subcategory']] = $select_row['id'];
            }
            $delete_query = "DELETE FROM  store_products WHERE store_id = '".$store_id."'";
            $delete_result = $db_conn->query($delete_query);
            if(!$delete_result)
              die("<p>Error rewriting data.</p>\n");
            $insert_query = "";
            foreach($links as $index => $link){
              if($link[0]=="" || $link[1]=="" || $link[2]==""){
                echo "<p style='color:red'>".$link[0]." Row not saved. Incomplete data.</p>";
                continue;
              }
              if(!array_key_exists($link[0],$subcategory_array)){
                  $sub_insert = "insert into categories(category,subcategory) values('".$category."','".$link['0']."')";
                  $db_conn->query($sub_insert);
                  $sub_id = $db_conn->insert_id;
                  $insert_query .= "('".$sub_id."','".$store_id."','".$link[1]."','".$link[2]."'),";
              }
              else{
                $insert_query .= "('".$subcategory_array[$link[0]]."','".$store_id."','".$link[1]."','".$link[2]."'),";
              }
            }

            if($insert_query!=''){
              $insert_query = "INSERT IGNORE INTO store_products(category_id,store_id,varieties,price) VALUES".$insert_query;
              $insert_query = rtrim($insert_query, ",");
              $insert_result = $db_conn->query($insert_query);
              if(!$insert_result)
                  die("<p style='color:red'>Error saving data.</p>\n");
            }
            echo "<p style='color:green'>Successfully saved data to database.</p>";
            
          }

            $select_query_1 = "SELECT category_id, subcategory, store_id, varieties, price FROM store_products sp LEFT JOIN categories c ON sp.category_id = c.id WHERE sp.store_id =  '$store_id' and c.category='".$category."'";
            $select_result_1 = $db_conn->query($select_query_1);
            $data_array = array();
            while($select_row_1 = $select_result_1->fetch_assoc()){
              $data_array[$select_row_1['subcategory']] = array($select_row_1['varieties'],$select_row_1['price']);
            }

          $index = 0;
          $placeholders = array("{index}", "{subcategory}", "{count}","{price}");
        ?>
      <div class="option filter">
        <h3>Add Subcategores here</h3>
        <table>
          <tbody>
            <?php
              foreach ($data_array as $key => $value){
                $values = array($index, htmlentities($key), htmlentities($value[0]), htmlentities($value[1]));
                echo str_replace($placeholders, $values, $html) . "\n";
                $index++;
              }
            ?>
          </tbody>
        </table>
        <input id="addbutton" type="button" value="Add Link"/>
      </div>
      <?php
          }
      ?>
    </form>

    <script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.1.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function () {
        var template = "",
            rowIndex = $("#form tr").length;
        $.ajax({
          url: "template.html"
        }).done(function (html) {
          template = html.replace(/{subcategory}|{count}|{price}/g, "");
        });
        $(document).on("change", "#subcategory", function () {
          if ($(this).val() !== "null")
            $("#form").submit();
        });
        $(document).on("click", ".deletebutton", function () {
          $(this).closest("tr").remove();
        });
        $(document).on("click", "#addbutton", function () {
          $("#form tbody").append(template.replace(/{index}/g, rowIndex++));
        });
        $(document).on("click", "#savebutton", function () {
          if ($("#subcategory").val() !== "null") {
            $("#action").val("save");
            $("#form").submit();
          }
        });
        $(document).on("submit", "#form", function () {
          $(".option.logic input[type='checkbox']:checked").each(function () {
            var $this = $(this);
            $this.val($this.data("value"));
          });
          return true;
        });
      });
    </script>
  </body>
</html>
<?php
}



$db_conn->close();
?>