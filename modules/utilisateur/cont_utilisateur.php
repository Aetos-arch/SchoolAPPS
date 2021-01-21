<?php

require_once 'modules/generique/cont_generique.php';
require_once 'vue_utilisateur.php';
require_once 'modele_utilisateur.php';

class ContUtilisateur extends ContGenerique
{

	public function __construct()
	{
		parent::__construct(new ModeleUtilisateur(), new VueUtilisateur());
	}

	public function accueilUtilisateur($moduleContent)
	{
		$this->vue->pageAccueilUtilisateur($moduleContent);
	}

	public function tableauBord()
	{
		$this->vue->tableauBord();
	}

	public function nouveauLogin()
	{
		$this->vue->nouveauLogin();
		if (isset($_POST['nouveauLogin'])) {
			$nouveauLogin = addslashes(strip_tags($_POST['nouveauLogin']));
			if ($this->modele->loginExiste($nouveauLogin) != 0) {
				$this->loginExiste();
				header('');
				exit();
			} else {
				$this->modele->setLogin($_SESSION['idUtil'], $nouveauLogin);
				$_SESSION['nomUser'] = $nouveauLogin;
				header('');
				exit();
			}
		}
	}

	public function nouveauMotDePasse()
	{
		$this->vue->nouveauMotDePasse();
		if (isset($_POST['nouveau_password2'])) {
			$nouveauMotDePasse1 = addslashes(strip_tags($_POST['nouveau_password1']));
			$nouveauMotDePasse2 = addslashes(strip_tags($_POST['nouveau_password2']));
			if ($nouveauMotDePasse1 == $nouveauMotDePasse2 && $nouveauMotDePasse1 != "") {
				$passNow = $this->modele->getPassword($_SESSION['idUtil']);
				if (password_verify($_POST['old_password'], $passNow)) {
					$nouveauMotDePasseHash = password_hash($nouveauMotDePasse1,  PASSWORD_BCRYPT);
					$this->modele->setPass($nouveauMotDePasseHash, $_SESSION['idUtil']);
					header('');
					exit();
				} else {
					$this->loginExiste();
				}
			} else {
				$this->motDePasseNonIdentique();
			}
		}
	}


	public function menu()
	{
		$this->vue->afficherMenu();
	}

	public function getMessages($idTicket, $isJson)
	{
		if ($isJson) {
			$result = $this->modele->getMessages($idTicket);
			$this->vue->json($result);
			header('Content-Type: application/json');
			exit();
		} else {
			$this->vue->chat();
		}
	}

	public function envoyerMessage($idTicket, $message)
	{
		$result = [
			'idAuteur' => $_SESSION['idUtil'],
			'idTicket' => $idTicket,
			'message' => $message
		];
		$this->modele->envoyerMessage($result);
	}


	public function nouveauTicket()
	{
		if (isset($_POST['explication'])) {
			$result = [
				'explication' => addslashes(strip_tags($_POST['explication'])),
				'intitule' => addslashes(strip_tags($_POST['intitule'])),
				'idProduit' => addslashes(strip_tags($_POST['idProduit'])),
				'idUtilisateur' => $_SESSION['idUtil']
			];
			try {
				$this->verifTableauValeurNull($result);
				$this->modele->creerTicket($result);
			} catch (Exception $e) {
				$e->getMessage("");
			}
		} else {
			$this->vue->nouveauTicket();
		}
	}

	public function afficheTickets()
	{
		$result = $this->modele->getTickets(($_SESSION['idUtil']));
		$this->vue->afficheTickets($result);
	}

	public function afficheTicket()
	{
		$idTicket = strip_tags($_POST['idTicket']);
		$result = $this->modele->getTicket($idTicket);
		$this->vue->afficheTicket($result);
	}

	public function afficheCommandes()
	{
		//$commandes = $this->modele->getCommandes($_SESSION['idUtil']);
		$commandes = $this->modele->getCommandes(1);
		var_dump($commandes);
		$this->vue->afficheCommandes($commandes);
	}

	public function afficheCommande()
	{
		$idCommande = strip_tags($_POST['idCommande']);
		$result = $this->modele->getTicket($idCommande);
		$this->vue->afficheCommande($result);
	}

	public function donnerAvis($nomProduit)
	{
		$idProduit = $this->modele->getIdProduit($nomProduit);
		$avisExiste = $this->modele->avisExiste($_SESSION['idUtilisateur'], $idProduit);
		if ($avisExiste != 0) {
			echo "avis existe déjà";
		} else if (isset($_POST['commentaire'])) {
			$result = [
				'idUtilisateur' => $_SESSION['idUtilisateur'],
				'idProduit' => addslashes(strip_tags($idProduit)),
				'titre' => addslashes(strip_tags($_POST['titre'])),
				'commentaire' => addslashes(strip_tags($_POST['commentaire'])),
				'note' => addslashes(strip_tags($_POST['note']))
			];
			$this->modele->donnerAvis($result);
		} else {
			$this->vue->formDonnerAvis();
		}
	}


	public function supprimerAvis()
	{
		$idAvis = $_POST['idAvis'];
		$this->modele->supprimerAvis($idAvis);
		// redirection
	}

	public function modifierAvis()
	{
		if (isset($_POST['commentaire'])) {
			$result = [
				'idUtilisateur' => $_SESSION['idUtilisateur'],
				'idProduit' => strip_tags($_POST['idProduit']),
				'titre' => addslashes(strip_tags($_POST['titre'])),
				'commentaire' => addslashes(strip_tags($_POST['commentaire'])),
				'note' => addslashes(strip_tags($_POST['note']))
			];
			$this->modele->donnerAvis($result);
		} else {
			$idAvis = $_POST['idAvis'];
			$result = $this->modele->getAvis($idAvis);
			$this->vue->formModifierAvis($result);
		}
	}
}
