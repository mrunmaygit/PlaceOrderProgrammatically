/**
 * Variables declaration
 **/
 var index = 1;

 /**
  * Clear form
  **/
  function clearForm(){
  	 document.getElementById('orderplaceform').reset();
  }

/**
 * Render new Product row
 **/
 function addMoreProducts(){
 	index++;
 	$("#formtable").append('<tr id="row'+index+'"><td> <div class="form-group"><label for="productid'+index+'">Product '+index+' ID</label><div class="form-group"></td><td> <div class="form-group"><input class="form-control" name="productid'+index+'" id="productid'+index+'" type="text"/><button class="btn btn-danger" onclick="deleteRow('+index+')" class="glyphicon glyphicon-trash" aria-hidden="true">Delete</span> </div></td></tr><tr id="rowqty'+index+'"><td><div class="form-group"> <label for="productqty'+index+'">Product '+index+' Quantity</label></div></td><td> <div class="form-group"> <input class="form-control" name="productqty'+index+'" id="productqty'+index+'" type="text"/> </div></td></tr>')
 
 }

 /**
 * delete row
 **/
 function deleteRow(rowindex){
 	$("#row"+rowindex).remove();
 	$("#rowqty"+rowindex).remove();
 	index--;
 }

/**
 * Submit form to PHP script with AJAX
 **/
 function submitForm(){
 	$(document).ready(function(){
 		$.ajax({
 			url : "http://localhost/PlaceOrder/script/placeOrder.php",
 			data : $("#orderplaceform").serialize()+"&index="+index,
 			dataType : "json",
 			type : "post"
 		}).done(function(result){
 			if(result.result=="success"){
 				$("#modalheader").css("background-color","#74d85b");
 				$("#modaltitle").html("Success");
 				$("#modalbody").html("Order ID : "+result.order_id);
 				$("#resultmodal").modal("show");
 			}
 			else{
 				$("#modalheader").css("background-color","#fc4c2d");
 				$("#modaltitle").html("Error");
 				$("#modalbody").html(result.result);
 				$("#resultmodal").modal("show");
 			}
 		});	
 	});

 }