<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class basket_item
{
  public $product;

  public $item;

  public $quantity;

  public $cost = 0;

  public $cost_per = 0;

  public $items;

  public function __construct($aProduct, $aItem, $aQuantity){
    // TODO check individual product.
    $this->product = $aProduct;
    $this->item = $aItem;
    $this->quantity = $aQuantity;
    $this->calculate_cost();
  }

  private function calculate_cost(){
    $prod = ORM::factory("product", $this->product);
    $this->cost = $prod->cost * $this->quantity;
    $this->cost_per = $prod->cost;
  }

  public function add($quantity){
    $this->quantity += $quantity;
    $this->calculate_cost();
  }

  public function size(){
    return $this->quantity;
  }

  public function getItem(){
     $photo = ORM::factory("item", $this->item);
     return $photo;
  }

  public function product_description(){
     $prod = ORM::factory("product", $this->product);
     return $prod->description;
  }

  public function getCode(){
     $photo = ORM::factory("item", $this->item);
     $prod = ORM::factory("product", $this->product);
     return $photo->id." - ".$photo->title." - ".$prod->name;
  }

}

class Session_Basket_Core {

  public $contents = array();

  public $name = "";
  public $house = "";
  public $street = "";
  public $suburb = "";
  public $town = "";
  public $postcode = "";
  public $email = "";
  public $phone = "";

  public function clear(){
    if (isset($this->contents)){
      foreach ($this->contents as $key => $item){
        unset($this->contents[$key]);
      }
    }
  }

  private function create_key($product, $id){
    return "$product _ $id";
  }

  public function size(){
    $size = 0;
    if (isset($this->contents)){
      foreach ($this->contents as $product => $basket_item){
        $size += $basket_item->size();
      }
    }
    return $size;
  }

  public function add($id, $product, $quantity){

    $key = $this->create_key($product, $id);
    if (isset($this->contents[$key])){
      $this->contents[$key]->add($id, $quantity);
    }
    else {
      $this->contents[$key] = new basket_item($product, $id, $quantity);
    }
  }

  public function remove($key){
    unset($this->contents[$key]);
  }

  public function cost(){
    $cost = 0;
    if (isset($this->contents)){
      foreach ($this->contents as $product => $basket_item){
        $cost += $basket_item->cost;
      }
    }
    return $cost;
  }

  public static function get(){
    return Session::instance()->get("basket");
  }

  public static function getOrCreate(){
    $session = Session::instance();

    $basket = $session->get("basket");
    if (!$basket)
    {
      $basket = new Session_Basket();
      $session->set("basket", $basket);
    }
    return $basket;
  }

}