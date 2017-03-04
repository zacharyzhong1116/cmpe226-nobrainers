<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Assighment#4 NoBrainers</title>
</head>

<body>
    <?php
        function constructTable($data)
        {
            // We're going to construct an HTML table.
            print "    <table border='1'>\n";
                
            // Construct the HTML table row by row.
            $doHeader = true;
            foreach ($data as $row) {
                    
                // The header row before the first data row.
                if ($doHeader) {
                    print "        <tr>\n";
                    foreach ($row as $name => $value) {
                        print "            <th>$name</th>\n";
                    }
                    print "        </tr>\n";
                    $doHeader = false;
                }
                    
                // Data row.
                print "        <tr>\n";
                foreach ($row as $name => $value) {
                    print "            <td>$value</td>\n";
                }
                print "        </tr>\n";
            }
            
            print "    </table>\n";
        }
    
        $id = filter_input(INPUT_GET, "id");
        $recipe = filter_input(INPUT_GET, "recipe");
        
        try {
            if (!(filter_var($id, FILTER_VALIDATE_INT) === 0 || !filter_var($id, FILTER_VALIDATE_INT) === false)) { 
            // fix bug: conflict with zero and FILTER_VALIDATE_INT
                throw new Exception("Missing id.");
            }
            if (empty($recipe)) {
                throw new Exception("Missing recipe name.");
            }
                
        
            // Connect to the database.
            $con = new PDO("mysql:host=localhost;dbname=nobrainers",
                           "nobrainers", "sesame");
            $con->setAttribute(PDO::ATTR_ERRMODE,
                               PDO::ERRMODE_EXCEPTION);
            
            $query1 = "SELECT * FROM product WHERE ProductID IN (SELECT ProductID 
                                                                FROM iscontained
                                                                WHERE RecipeName = :recipe AND CustomerID = :id
                                                                )";
            $query2 = "SELECT FirstName, LastName FROM customer WHERE CustomerID = :id";

            $ps1 = $con->prepare($query1);
            $ps2 = $con->prepare($query2);

            // Fetch the matching row.
            $ps1->execute(array(':id' => $id, ':recipe' => $recipe));
            $ps2->execute(array(':id' => $id));
            $data1 = $ps1->fetchAll(PDO::FETCH_ASSOC);
                        
            // $data is an array.
            if (count($data1) > 0) {
                while($data = $ps2->fetch( PDO::FETCH_ASSOC )){ 
                    print "<h1>Products included in \"".$recipe."\" shared by \"".$data['FirstName']." ".$data['LastName']."\"</h1>\n"; 
                }

                constructTable($data1);
            }
            else {
                print "<h3>(No match.)</h3>\n";
            }
        }
        catch(PDOException $ex) {
            echo 'ERROR: '.$ex->getMessage();
        }    
        catch(Exception $ex) {
            echo 'ERROR: '.$ex->getMessage();
        }
    ?>
</body>
</html>