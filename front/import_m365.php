<?php

include("../../../inc/includes.php");

global $conn, $sql;
//var_dump("importation déléments Microsoft 365");
Function import_m365 ($csvFile, $entities_id); {
	// Chemin vers le fichier CSV
	$csvFile = '"C:\\Users\\jclai\\Downloads\\users_21_03_2024.csv"';

	// Ouvrir le fichier en mode lecture
	$file = fopen($csvFile, 'r');
	$entities_id = 0 ; 

	// Vérifier si le fichier est ouvert avec succès
	var_dump ($file);die;
	if (!$file) {
    die("Erreur lors de l'ouverture du fichier CSV.");
	}

	// Lire les lignes du fichier une par une et insérer dans la table SQL
	while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
		// Préparer la requête d'insertion
		$sql = "INSERT INTO IGNORE glpi_users (nickname, firstname, realname, entities_id, comment, microsoft_guid, phone, phone2, name, creation_date) VALUES ('" . $data[5] . "', '" . $data[7] . "', '" . $data[9] . ",
	, $entities_id, '" . $data[12] . "', '" . $data[13] . "', '" . $data[15] . "', '" . $data[13] . "', '" . $data[18] . "', '" . $data[30] . "', '" . $data[31] . "')";

		// Exécuter la requête d'insertion
		if ($conn->query($sql) === TRUE) {
			echo "Enregistrement inséré avec succès.\n";
		} else {
			echo "Erreur lors de l'insertion de l'enregistrement : " . $conn->error . "\n";
		}
	}

// Fermer le fichier
fclose($file);

// Fermer la connexion à la base de données
// $conn->close();

	// Session::addMessageAfterRedirect($table);

	echo "<script>window.location.href='config.form.php';</script>";// revient sur la page
	Session::addMessageAfterRedirect("Importation des utilisateurs effectuée");
}
?>
