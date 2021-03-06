<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property string $userID
 * @property string $userName
 * @property string $firstName
 * @property string $lastName
 * @property string $emailAddress
 * @property string $phoneNumber
 * @property string $status
 * @property string $dateCreated
 * @property string $createdBy
 * @property string $dateModified
 * @property string $modifiedBy
 */
class Users extends GenericAR {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Users the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'users';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('userName, firstName, lastName, status, dateCreated, createdBy, dateModified, modifiedBy', 'required'),
            array('userName', 'length', 'max' => 30),
            array('firstName, lastName', 'length', 'max' => 45),
            array('emailAddress', 'length', 'max' => 100),
            array('emailAddress', 'email'),
            array('emailAddress, phoneNumber', 'unique'),
            array('phoneNumber', 'length', 'max' => 15),
            array('status, createdBy, modifiedBy', 'length', 'max' => 11),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('userID, userName, firstName, lastName, emailAddress, phoneNumber, status, dateCreated, createdBy, dateModified, modifiedBy', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'createdBy_' => array(self::BELONGS_TO, 'Users', 'createdBy'),
            'modifiedBy_' => array(self::BELONGS_TO, 'Users', 'modifiedBy'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'userID' => 'User',
            'userName' => 'User Name',
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'emailAddress' => 'Email Address',
            'phoneNumber' => 'Phone Number',
            'status' => 'Status',
            'dateCreated' => 'Date Created',
            'createdBy' => 'Created By',
            'dateModified' => 'Date Modified',
            'modifiedBy' => 'Modified By',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('userID', $this->userID);
        $criteria->compare('userName', $this->userName, true);
        $criteria->compare('firstName', $this->firstName, true);
        $criteria->compare('lastName', $this->lastName, true);
        $criteria->compare('emailAddress', $this->emailAddress, true);
        $criteria->compare('phoneNumber', $this->phoneNumber, true);
        $criteria->compare('status', $this->status);
        $criteria->compare('dateCreated', $this->dateCreated, true);
        $criteria->compare('createdBy', $this->createdBy);
        $criteria->compare('dateModified', $this->dateModified, true);
        $criteria->compare('modifiedBy', $this->modifiedBy);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
    
    /**
     * function to get a user's full name
     * @return type
     */
    public function getFullName(){
        return trim($this->firstName. ' '. $this->lastName);
    }
    
    /**
     * override this here to perform some additional stuff, i.e. send link to 
     * user's account
     */
    public function afterSave() {
        $userID = $this->userID;
        if($this->isNewRecord){
            //for new user records, they should get a notification to allow them
            //to set their passwords
            $notificationSent = NotificationUtils::sendNewAccountNotification($userID, $emailAlert = true, $SMSAlert = true, $validateUser = false);
        }
//        $notificationSent = Utils::
        return parent::afterSave();
    }
    
    public function beforeValidate() {
        //validate phone number, and convert it to int'l format
        if(isset($this->phoneNumber) && $this->phoneNumber != ''){
            $MSISDN = $this->phoneNumber;
            $validMSISDN = Utils::isValidMobileNo($MSISDN);
            if($validMSISDN == 0){
                $this->addError('phoneNumber', 'Invalid mobile number');
                return false;
            }
            $this->phoneNumber = $validMSISDN;
        }
        return parent::beforeValidate();
    }
}