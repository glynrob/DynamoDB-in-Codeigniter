<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends CI_Model {

	protected $primaryKey = 'id';
	protected $tableName = 'users';
	
    function __construct() {
        parent::__construct();
		
		// connect to the SDK
		include_once($this->config->item('dynamodb').'sdk.class.php');
		
		// set credentials
		include (APPPATH.'/config/aws.inc.php');
		
    }
	
	/** Insert new record */
	function save($member='') {
		$dynamodb = new AmazonDynamoDB();
		if ($member != ''){
			if (!isset($member['id'])){ // new record
				$id = time().rand(0,100);
				$dynamodb->batch($queue)->put_item(array(
					'TableName' => $this->tableName,
					'Item' => array(
						$this->primaryKey => array( AmazonDynamoDB::TYPE_NUMBER => $id ), // Primary (Hash) Key
						'date' => array( AmazonDynamoDB::TYPE_NUMBER => (string)$member['date'] ),
						'name' => array( AmazonDynamoDB::TYPE_STRING => $member['name'] ),
						'address' => array( AmazonDynamoDB::TYPE_STRING => $member['address'] ),
						'phone' => array( AmazonDynamoDB::TYPE_STRING => $member['phone'] )
					)
				));
					 	
				// Execute the batch of requests in parallel if you wish
				$responses = $dynamodb->batch($queue)->send();
				if ($responses[0]->status == '200'){
					return $id;
				} else {
					print_r($responses);
				}
			} else { // edit existing record
				$id = $member['id'];
				$response = $dynamodb->update_item(array(
					'TableName' => $this->tableName,
					'Key' => array(
						'HashKeyElement' => array( // "id" column
							AmazonDynamoDB::TYPE_NUMBER => $id
						)
						/*,
						'RangeKeyElement' => array( // "date" column
							AmazonDynamoDB::TYPE_NUMBER => $current_time
						)*/
					),
					'AttributeUpdates' => array(
						'date' => array(
							'Action' => AmazonDynamoDB::ACTION_PUT,
							'Value' => array(AmazonDynamoDB::TYPE_NUMBER => (string)$member['date'])
						),
						'name' => array(
							'Action' => AmazonDynamoDB::ACTION_PUT,
							'Value' => array(AmazonDynamoDB::TYPE_STRING => $member['name'])
						),
						'address' => array(
							'Action' => AmazonDynamoDB::ACTION_PUT,
							'Value' => array(AmazonDynamoDB::TYPE_STRING => $member['address'])
						),
						'phone' => array(
							'Action' => AmazonDynamoDB::ACTION_PUT,
							'Value' => array(AmazonDynamoDB::TYPE_STRING => $member['phone'])
						)
					)
				));
				if ($response->status == '200'){
					return $id;
				} else {
					print_r($responses);
				}
			}
		}
    }
	
	/** Fetches all records with limit and orderby values's */
	function getAll($limit='') {
		$dynamodb = new AmazonDynamoDB();
		$query = array(
			'TableName' => $this->tableName, 
			//'AttributesToGet' => array('id','name','address','phone','date'),
		);
		if ($limit != ''){$query['Limit'] = $limit;}
		$scan_response = $dynamodb->scan($query);
		if ($scan_response->status == '200'){
			return $scan_response->body->Items;
		} else {
			print_r($scan_response);
		}
    }
	
	/** Example using the count function */
	function countAll(){
		$dynamodb = new AmazonDynamoDB();
		$query = array(
			'TableName' => $this->tableName
		);
		$query['Count'] = true;
		$scan_response = $dynamodb->scan($query);
		if ($scan_response->status == '200'){
			 return $scan_response->body->ScannedCount;
		} else {
			print_r($scan_response);
		}
	}
	

    /** Fetches a record by its' passed field and values's */
    function getByID($id='') {
		if ($id != ''){
			$dynamodb = new AmazonDynamoDB();
			$query = array(
				'TableName' => $this->tableName, 
				//'AttributesToGet' => array('id','name','address','phone','date'),
				'Key' => array(
					'HashKeyElement' => array( AmazonDynamoDB::TYPE_NUMBER => $id )
				)
			);
			$scan_response = $dynamodb->get_item($query);
			if ($scan_response->status == '200'){
				 return $scan_response->body->Item;
			} else {
				print_r($responses);
			}
		}
        return false;
    }

    /** Fetches a record by its' passed field and values's */
    function getByColumn($field='id', $value='') {
		$dynamodb = new AmazonDynamoDB();
		$scan_response = $dynamodb->scan(array(
			'TableName' => $this->tableName, 
			//'AttributesToGet' => array('name'),
			'ScanFilter' => array( 
				$field => array(
					'ComparisonOperator' => AmazonDynamoDB::CONDITION_EQUAL,
					'AttributeValueList' => array(
						array( AmazonDynamoDB::TYPE_STRING => (string)$value )
					)
				),
			)
		));
		if ($scan_response->status == '200'){
			return $scan_response->body->Items;
		} else {
			print_r($scan_response);
		}
    }
	
    /** Deletes a record by it's primary key */
    function deleteById($id='') {
		if ($id != ''){
			$dynamodb = new AmazonDynamoDB();
			$response = $dynamodb->delete_item(array(
				'TableName' => $this->tableName,
				'Key' => array(
					'HashKeyElement' => array( // "id" column
						AmazonDynamoDB::TYPE_NUMBER => $id
					)
					/*,
					'RangeKeyElement' => array( // "date" column
						AmazonDynamoDB::TYPE_NUMBER => $current_time
					)*/
				)
			));
			if ($response->isOK())// Check for success...
			{
				return true;
			}
			else
			{
				print_r($response);
				return false;
			}
		}
		return false;
    }
	
	function table_exists(){
		// Instantiate the class.
		$dynamodb = new AmazonDynamoDB();
		$response = $dynamodb->describe_table(array(
			'TableName' => $this->tableName
		));
		if ((string) $response->body->Table->TableStatus === 'ACTIVE'){
			return true;
		} else {
			return false;
		}
	}
	
	function create_table(){
		// Instantiate the class.
		$dynamodb = new AmazonDynamoDB();
		$response = $dynamodb->create_table(array(
			'TableName' => $this->tableName,
			'KeySchema' => array(
				'HashKeyElement' => array(
					'AttributeName' => $this->primaryKey,
					'AttributeType' => AmazonDynamoDB::TYPE_NUMBER
				)
				/* ONLY REQUIRED IF WANTING A Hash and Range table
				,
				'RangeKeyElement' => array(
					'AttributeName' => 'date',
					'AttributeType' => AmazonDynamoDB::TYPE_NUMBER
				)*/
			),
			'ProvisionedThroughput' => array(
				'ReadCapacityUnits' => 5,
				'WriteCapacityUnits' => 5
			)
		));
			 
		// Check for success...
		if ($response->isOK()){
			// continue
		} else {
			echo '# A ERROR HAS OCCURED<br />';
			print_r($response);
			return false;
		}  
			 
		####################################################################
		# Sleep and poll until the table has been created
			 
		$count = 0;
		do {
			sleep(1);
			$count++;
			$response = $dynamodb->describe_table(array(
				'TableName' => $this->tableName
			));
		}
		while ((string) $response->body->Table->TableStatus !== 'ACTIVE');
		return true;
	}

}