<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php include 'include\dbconfig.php'?>
<div class="container">

<h2 class="my-3">My watchlist</h2>

<?php

$results_per_page = 10;



	$id = $_SESSION['user']['id'];

	$watchlist_listing_query = "SELECT * FROM tbl_listings as listings WHERE listings.listing_id IN (SELECT listing_id FROM watchlist WHERE user_id = $id) LIMIT $results_per_page";

	$num_watchlist_query = "SELECT COUNT(listing_id) FROM watchlist WHERE user_id = $id";

	$num_watchlist_result = mysqli_query($mysqli, $num_watchlist_query)
			or die('Error making watchlist count query');
	$row = mysqli_fetch_array($num_watchlist_result);


	if ($row[0] < 1) {
		$max_page = 1;
	}
	else {
		$max_page = ceil($row[0] / $results_per_page);
	}
	if (!isset($_GET['page']))
		{
		$curr_page = 1;
		}
	else
	{
		if ($_GET['page'] == 1)
		{
			$curr_page = 1;
		}
		else
		{
			//This limits the number of answers per page to $results_per_page, and ensures not the same 'x' results are printed on each page using SQL 'offset'
			$curr_page = $_GET['page'];
			$offset = ($curr_page*$results_per_page)-$results_per_page;
			$watchlist_listing_query .= " OFFSET $offset";

		}
	}
?>

<div class="container mt-5">

<?php
if ($row[0]  < 1) {
		echo ("Sorry, it seems you have not added any items to your watchlist.");
	}
?>


<ul class="list-group">


<?php
	//Get results of $query_ordered so we can print to user
	$result = mysqli_query($mysqli, $watchlist_listing_query)
		or die('Error making recco select id query');

	while ($row = mysqli_fetch_array($result))
	{
		//Count the number of bids
		$count_bid_query = "SELECT COUNT(*) FROM biding WHERE listing_id = {$row['listing_id']}";

		$count_bid_result = mysqli_query($mysqli, $count_bid_query)
			or die('Error making top bid query');

		$bid_count = mysqli_fetch_array($count_bid_result);

		//Use print listing function to print the listings. If not bids then show start price, else show highest bid
		if ($bid_count[0] == 0) {
			print_listing_li($row['listing_id'], $row['title'], $row['details'], $row['starting_price'], $bid_count[0], date_create($row['end_date']));
		}
		else {

			$top_bid_query = "SELECT MAX(bidprice) FROM bids WHERE listing_id = {$row['listing_id']}";

			$top_bid_result = mysqli_query($mysqli, $top_bid_query)
				or die('Error making top bid query');

			$top_bid = mysqli_fetch_array($top_bid_result);

			print_listing_li($row['listing_id'], $row['item_title'], $row['itemdescription'], $top_bid[0], $bid_count[0], date_create($row['endtime']));

		}

	}
	mysqli_close($mysqli);


?>

</ul>

<!-- Pagination for results listings -->
<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">

<?php

  // Copy any currently-set GET variables to the URL.
  $querystring = "";
  foreach ($_GET as $key => $value) {
    if ($key != "page") {
      $querystring .= "$key=$value&amp;";
    }
  }

  $high_page_boost = max(3 - $curr_page, 0);
  $low_page_boost = max(2 - ($max_page - $curr_page), 0);
  $low_page = max(1, $curr_page - 2 - $low_page_boost);
  $high_page = min($max_page, $curr_page + 2 + $high_page_boost);

  if ($curr_page != 1) {
    echo('
    <li class="page-item">
      <a class="page-link" href="mywatchlist.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
  }

  for ($i = $low_page; $i <= $high_page; $i++) {
    if ($i == $curr_page) {
      // Highlight the link
      echo('
    <li class="page-item active">');
    }
    else {
      // Non-highlighted link
      echo('
    <li class="page-item">');
    }

    // Do this in any case
    echo('
      <a class="page-link" href="mywatchlist.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }

  if ($curr_page != $max_page) {
    echo('
    <li class="page-item">
      <a class="page-link" href="mywatchlist.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
  }
?>

  </ul>
</nav>


</div>



<?php include_once("footer.php")?>
