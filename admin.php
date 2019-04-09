<?php include('includes/header.php'); ?>
  
<?php include('includes/nav.php'); ?>

<div class="container">

	<div class="jumbotron">
		<h1 class="text-center"><?php if(logged_in()) {
			echo "LOGGED IN";
		} 
		else {
			redirect("index.php");
		} ?></h1>
	</div>


</div> <!--Container-->	

<?php include('includes/footer.php'); ?>