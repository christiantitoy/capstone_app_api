<?php
header("Content-Type: application/json");
require_once "../db_connection.php";

$type = $_GET['type'] ?? '';

switch($type){
  case "buyers":
    $sql = "SELECT id, username, email, avatar_url FROM buyers ORDER BY id DESC";
    $res = $conn->query($sql);
    $arr = []; while($row=$res->fetch_assoc()) $arr[]=$row;
    echo json_encode($arr);
    break;

  case "sellers":
    $sql = "SELECT s.id, s.fullname, s.shop_name, s.shop_description, s.business_address,
                   s.shop_category, s.business_type, s.is_approved, b.username AS buyer_username
            FROM seller_profiles s
            LEFT JOIN buyers b ON s.buyer_id=b.id
            ORDER BY s.id DESC";
    $res = $conn->query($sql);
    $arr = []; while($row=$res->fetch_assoc()) $arr[]=$row;
    echo json_encode($arr);
    break;

  case "products":
    $sql = "SELECT p.id, p.product_name, p.product_description, p.category, p.price, p.stock,
                   p.main_image_url, p.status, s.fullname AS seller_name, s.shop_name
            FROM products p
            LEFT JOIN seller_profiles s ON p.seller_id=s.id
            ORDER BY p.id DESC";
    $res = $conn->query($sql);
    $arr = []; while($row=$res->fetch_assoc()) $arr[]=$row;
    echo json_encode($arr);
    break;

  case "approve_seller":
    $id=intval($_POST['id']??0);
    if($id>0){
      $stmt=$conn->prepare("UPDATE seller_profiles SET is_approved=1 WHERE id=?");
      $stmt->bind_param("i",$id); $stmt->execute(); $stmt->close();
      echo json_encode(["success"=>true,"message"=>"Seller approved"]);
    }
    break;

  case "reject_seller":
    $id = intval($_POST['id'] ?? 0);
    if($id > 0){
        // Instead of 2, set back to 0 (pending)
        $stmt = $conn->prepare("UPDATE seller_profiles SET is_approved=0 WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success"=>true,"message"=>"Seller rejected and set to pending"]);
    }
    break;


  case "hold_product":
    $id=intval($_POST['id']??0);
    if($id>0){
      $stmt=$conn->prepare("UPDATE products SET status='on_hold' WHERE id=?");
      $stmt->bind_param("i",$id); $stmt->execute(); $stmt->close();
      echo json_encode(["success"=>true,"message"=>"Product on hold"]);
    }
    break;

  case "remove_product":
    $id=intval($_POST['id']??0);
    if($id>0){
      $stmt=$conn->prepare("UPDATE products SET status='removed' WHERE id=?");
      $stmt->bind_param("i",$id); $stmt->execute(); $stmt->close();
      echo json_encode(["success"=>true,"message"=>"Product removed"]);
    }
    break;

  case "restore_product":
    $id=intval($_POST['id']??0);
    if($id>0){
      $stmt=$conn->prepare("UPDATE products SET status='approved' WHERE id=?");
      $stmt->bind_param("i",$id); $stmt->execute(); $stmt->close();
      echo json_encode(["success"=>true,"message"=>"Product restored"]);
    }
    break;

    case "seller_details":
    $id = intval($_GET['id'] ?? 0);
    if($id > 0){
        // Get seller profile with buyer info
        $sql = "SELECT s.*, b.username as buyer_username, b.email, b.avatar_url
                FROM seller_profiles s
                LEFT JOIN buyers b ON s.buyer_id = b.id
                WHERE s.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $seller = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if($seller){
            // Get seller documents
            $docSql = "SELECT * FROM seller_documents WHERE seller_id = ?";
            $docStmt = $conn->prepare($docSql);
            $docStmt->bind_param("i", $id);
            $docStmt->execute();
            $documents = $docStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $docStmt->close();
            
            $seller['documents'] = $documents;
            echo json_encode($seller);
        } else {
            echo json_encode(["error"=>"Seller not found"]);
        }
    }
    break;

    case "seller_documents":
    $seller_id = intval($_GET['seller_id'] ?? 0);
    if($seller_id > 0){
        $sql = "SELECT * FROM seller_documents WHERE seller_id = ? ORDER BY uploaded_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $documents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode($documents);
    } else {
        echo json_encode(["error"=>"Invalid seller ID"]);
    }
    break;

  default:
    echo json_encode(["error"=>"Invalid request"]);
    break;
}
?>
