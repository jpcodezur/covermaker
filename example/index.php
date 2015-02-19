<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>CoverMaker</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <form id="create-cover" method="post" action="make.php">
            <div>    
                <input type="text" name="pelicula" placeholder="Nombre de la palicula">
            </div>
            <div>
                <input type="submit" value="Enviar">
            </div>
        </form>
    </body>
</html>
<style>
    form#create-cover{
        width:400px;
        margin:0 auto;
    }
</style>