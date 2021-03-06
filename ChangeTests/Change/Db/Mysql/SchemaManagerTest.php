<?php
namespace ChangeTests\Change\Db\Mysql;

use Change\Db\Mysql\DbProvider;
use Change\Db\Schema\FieldDefinition;
use \Change\Db\Mysql\SchemaManager;

class SchemaManagerTest extends \ChangeTests\Change\TestAssets\TestCase
{
	protected function setUp()
	{
		if (!in_array('mysql', \PDO::getAvailableDrivers()))
		{
			$this->markTestSkipped('PDO Mysql is not installed.');
		}

		$provider = $this->getApplicationServices()->getDbProvider();
		if (!($provider instanceof DbProvider))
		{
			$this->markTestSkipped('The Mysql DbProvider is not configured.');
		}

		$connectionInfos = $provider->getConnectionInfos();
		if (!isset($connectionInfos['database']))
		{
			$this->markTestSkipped('The Mysql database not defined!');
		}
	}

	/**
	 * @return \Change\Db\Mysql\SchemaManager
	 */
	public function testGetInstance()
	{
		$provider = $this->getApplicationServices()->getDbProvider();
		$schemaManager = $provider->getSchemaManager();
		$this->assertTrue($schemaManager->check());
		$this->assertNotNull($schemaManager->getName());

		$schemaManager->clearDB();
		$this->assertCount(0, $schemaManager->getTableNames());
		$this->assertNull($schemaManager->getTableDefinition('test'));
		return $schemaManager;
	}

	/**
	 * @depends testGetInstance
	 * @param \Change\Db\Mysql\SchemaManager $schemaManager
	 * @return \Change\Db\Mysql\SchemaManager
	 */
	public function testSystemSchema($schemaManager)
	{
		$systemSchema = $schemaManager->getSystemSchema();
		$this->assertInstanceOf('\Change\Db\Schema\SchemaDefinition', $systemSchema);
		$systemSchema->generate();
		$tables = $schemaManager->getTableNames();
		$this->assertContains('change_document', $tables);
		$this->assertContains('change_document_correction', $tables);
		$this->assertContains('change_document_deleted', $tables);
		$this->assertContains('change_document_metas', $tables);
		$this->assertContains('change_path_rule', $tables);
		return $schemaManager;
	}

	/**
	 * @depends testSystemSchema
	 * @param \Change\Db\Mysql\SchemaManager $schemaManager
	 * @return \Change\Db\Mysql\SchemaManager
	 */
	public function testGetFieldDbOptions($schemaManager)
	{
		$dbOptions = $schemaManager->getFieldDbOptions(\Change\Db\ScalarType::STRING);
		$this->assertArrayHasKey('length', $dbOptions);
		$this->assertEquals(255, $dbOptions['length']);

		$dbOptions = $schemaManager->getFieldDbOptions(\Change\Db\ScalarType::TEXT);
		$this->assertArrayHasKey('length', $dbOptions);
		$this->assertEquals(16777215, $dbOptions['length']);

		$dbOptions = $schemaManager->getFieldDbOptions(\Change\Db\ScalarType::DECIMAL);
		$this->assertArrayHasKey('precision', $dbOptions);
		$this->assertEquals(13, $dbOptions['precision']);
		$this->assertArrayHasKey('scale', $dbOptions);
		$this->assertEquals(4, $dbOptions['scale']);

		$dbOptions = $schemaManager->getFieldDbOptions(\Change\Db\ScalarType::BOOLEAN);
		$this->assertArrayHasKey('precision', $dbOptions);
		$this->assertEquals(3, $dbOptions['precision']);
		$this->assertArrayHasKey('scale', $dbOptions);
		$this->assertEquals(0, $dbOptions['scale']);

		$dbOptions = $schemaManager->getFieldDbOptions(\Change\Db\ScalarType::INTEGER);
		$this->assertArrayHasKey('precision', $dbOptions);
		$this->assertEquals(10, $dbOptions['precision']);
		$this->assertArrayHasKey('scale', $dbOptions);
		$this->assertEquals(0, $dbOptions['scale']);

		$dbOptions = $schemaManager->getFieldDbOptions(\Change\Db\ScalarType::DATETIME);
		$this->assertCount(0, $dbOptions);

		try
		{
			$schemaManager->getFieldDbOptions(-1);
			$this->fail('Invalid Field type: -1');
		}
		catch (\InvalidArgumentException $e)
		{
			$this->assertStringStartsWith('Invalid Field type:', $e->getMessage());
		}

		$dbOptions = $schemaManager->getFieldDbOptions(\Change\Db\ScalarType::STRING, array('test' => 8), array('length' => 50));
		$this->assertArrayHasKey('length', $dbOptions);
		$this->assertArrayHasKey('test', $dbOptions);
		$this->assertEquals(50, $dbOptions['length']);

		$dbOptions = $schemaManager->getFieldDbOptions(\Change\Db\ScalarType::STRING, array('length' => 8), array('length' => 50));
		$this->assertArrayHasKey('length', $dbOptions);
		$this->assertEquals(8, $dbOptions['length']);
		return $schemaManager;
	}

	/**
	 * @depends testGetFieldDbOptions
	 * @param \Change\Db\Mysql\SchemaManager $schemaManager
	 * @return \Change\Db\Mysql\SchemaManager
	 */
	public function testNewTableDefinition($schemaManager)
	{
		$td = $schemaManager->newTableDefinition('test');
		$this->assertEquals('test', $td->getName());
		$this->assertEquals('InnoDB', $td->getOption('ENGINE'));
		$this->assertEquals('utf8', $td->getOption('CHARSET'));
		return $schemaManager;
	}

	/**
	 * @depends testNewTableDefinition
	 * @param \Change\Db\Mysql\SchemaManager $schemaManager
	 * @return \Change\Db\Mysql\SchemaManager
	 */
	public function testNewField($schemaManager)
	{
		$td = $schemaManager->newTableDefinition('test_types');

		$fd = $schemaManager->newEnumFieldDefinition('enum', array('VALUES' => array('V1', 'V2', 'V3')));
		$this->assertEquals('enum', $fd->getName());
		$this->assertEquals(FieldDefinition::ENUM, $fd->getType());
		$this->assertCount(3, $fd->getOption('VALUES'));
		$td->addField($fd);

		try
		{
			$schemaManager->newEnumFieldDefinition('enum', array());
			$this->fail('Invalid Enum values');
		}
		catch (\InvalidArgumentException $e)
		{
			$this->assertStringStartsWith('Invalid Enum values', $e->getMessage());
		}

		$fd = $schemaManager->newCharFieldDefinition('char');
		$this->assertEquals(FieldDefinition::CHAR, $fd->getType());
		$this->assertEquals('char', $fd->getName());
		$this->assertEquals(255, $fd->getLength());

		$fd = $schemaManager->newCharFieldDefinition('char', array('length' => 10));
		$this->assertEquals('char', $fd->getName());
		$this->assertEquals(10, $fd->getLength());
		$td->addField($fd);


		$fd = $schemaManager->newVarCharFieldDefinition('varchar');
		$this->assertEquals(FieldDefinition::VARCHAR, $fd->getType());
		$this->assertEquals('varchar', $fd->getName());
		$this->assertEquals(255, $fd->getLength());

		$fd = $schemaManager->newVarCharFieldDefinition('varchar', array('length' => 10));
		$this->assertEquals('varchar', $fd->getName());
		$this->assertEquals(10, $fd->getLength());
		$td->addField($fd);

		$fd = $schemaManager->newNumericFieldDefinition('numeric');
		$this->assertEquals(FieldDefinition::DECIMAL, $fd->getType());
		$this->assertEquals('numeric', $fd->getName());
		$this->assertEquals(13, $fd->getPrecision());
		$this->assertEquals(4, $fd->getScale());
		$td->addField($fd);

		$fd = $schemaManager->newBooleanFieldDefinition('boolean');
		$this->assertEquals(FieldDefinition::SMALLINT, $fd->getType());
		$this->assertEquals('boolean', $fd->getName());
		$this->assertEquals(3, $fd->getPrecision());
		$this->assertEquals(0, $fd->getScale());
		$this->assertFalse($fd->getNullable());
		$this->assertEquals(0, $fd->getDefaultValue());
		$td->addField($fd);

		$fd = $schemaManager->newIntegerFieldDefinition('integer');
		$this->assertEquals(FieldDefinition::INTEGER, $fd->getType());
		$this->assertEquals('integer', $fd->getName());
		$this->assertEquals(10, $fd->getPrecision());
		$this->assertEquals(0, $fd->getScale());
		$td->addField($fd);

		$fd = $schemaManager->newFloatFieldDefinition('float');
		$this->assertEquals(FieldDefinition::FLOAT, $fd->getType());
		$this->assertEquals('float', $fd->getName());
		$td->addField($fd);

		$fd = $schemaManager->newTextFieldDefinition('text');
		$this->assertEquals(FieldDefinition::TEXT, $fd->getType());
		$this->assertEquals('text', $fd->getName());
		$this->assertEquals(16777215, $fd->getLength());
		$td->addField($fd);


		$fd = $schemaManager->newLobFieldDefinition('lob');
		$this->assertEquals(FieldDefinition::LOB, $fd->getType());
		$this->assertEquals('lob', $fd->getName());
		$this->assertEquals(16777215, $fd->getLength());
		$td->addField($fd);

		$fd = $schemaManager->newDateFieldDefinition('date');
		$this->assertEquals(FieldDefinition::DATE, $fd->getType());
		$this->assertEquals('date', $fd->getName());
		$td->addField($fd);

		$fd = $schemaManager->newTimeStampFieldDefinition('timestamp');
		$this->assertEquals(FieldDefinition::TIMESTAMP, $fd->getType());
		$this->assertEquals('timestamp', $fd->getName());
		$this->assertEquals('CURRENT_TIMESTAMP', $fd->getDefaultValue());
		$this->assertFalse($fd->getNullable());
		$td->addField($fd);

		$schemaManager->createTable($td);

		return $schemaManager;
	}


	/**
	 * @depends testNewField
	 * @param \Change\Db\Mysql\SchemaManager $schemaManager
	 * @return \Change\Db\Mysql\SchemaManager
	 */
	public function testGetTableDefinition($schemaManager)
	{
		$td = $schemaManager->getTableDefinition('test_types');
		$this->assertNotNull($td);
		$this->assertTrue($td->isValid());

		$fd = $td->getField('enum');
		$this->assertNotNull($fd);
		$this->assertEquals(FieldDefinition::ENUM, $fd->getType());
		$this->assertCount(3, $fd->getOption('VALUES'));
		$this->assertTrue($fd->getNullable());

		$fd = $td->getField('char');
		$this->assertNotNull($fd);
		$this->assertEquals(FieldDefinition::CHAR, $fd->getType());
		$this->assertEquals('char', $fd->getName());
		$this->assertEquals(10, $fd->getLength());
		$this->assertTrue($fd->getNullable());


		$fd = $td->getField('varchar');
		$this->assertNotNull($fd);
		$this->assertEquals(FieldDefinition::VARCHAR, $fd->getType());
		$this->assertEquals('varchar', $fd->getName());
		$this->assertEquals(10, $fd->getLength());
		$this->assertTrue($fd->getNullable());

		$fd = $td->getField('numeric');
		$this->assertNotNull($fd);
		$this->assertEquals(FieldDefinition::DECIMAL, $fd->getType());
		$this->assertEquals('numeric', $fd->getName());
		$this->assertEquals(13, $fd->getPrecision());
		$this->assertEquals(4, $fd->getScale());
		$this->assertTrue($fd->getNullable());
		$this->assertEquals(0, $fd->getDefaultValue());

		$fd = $td->getField('boolean');
		$this->assertNotNull($fd);
		$this->assertEquals(FieldDefinition::SMALLINT, $fd->getType());
		$this->assertEquals('boolean', $fd->getName());
		$this->assertEquals(3, $fd->getPrecision());
		$this->assertEquals(0, $fd->getScale());
		$this->assertFalse($fd->getNullable());

		$fd = $td->getField('integer');
		$this->assertNotNull($fd);
		$this->assertEquals(FieldDefinition::INTEGER, $fd->getType());
		$this->assertEquals('integer', $fd->getName());
		$this->assertEquals(10, $fd->getPrecision());
		$this->assertEquals(0, $fd->getScale());
		$this->assertTrue($fd->getNullable());

		$fd = $td->getField('float');
		$this->assertNotNull($fd);
		$this->assertEquals(FieldDefinition::FLOAT, $fd->getType());
		$this->assertEquals('float', $fd->getName());
		$this->assertTrue($fd->getNullable());

		$fd = $td->getField('text');
		$this->assertNotNull($fd);
		$this->assertEquals(FieldDefinition::TEXT, $fd->getType());
		$this->assertEquals('text', $fd->getName());
		$this->assertEquals(16777215, $fd->getLength());
		$this->assertTrue($fd->getNullable());

		$fd = $td->getField('date');
		$this->assertNotNull($fd);
		$this->assertEquals(FieldDefinition::DATE, $fd->getType());
		$this->assertEquals('date', $fd->getName());
		$this->assertTrue($fd->getNullable());

		$fd = $td->getField('timestamp');
		$this->assertNotNull($fd);
		$this->assertEquals(FieldDefinition::TIMESTAMP, $fd->getType());
		$this->assertEquals('timestamp', $fd->getName());
		$this->assertEquals('CURRENT_TIMESTAMP', $fd->getDefaultValue());
		$this->assertFalse($fd->getNullable());

		return $schemaManager;
	}

	/**
	 * @depends testGetTableDefinition
	 * @param \Change\Db\Mysql\SchemaManager $schemaManager
	 * @return \Change\Db\Mysql\SchemaManager
	 */
	public function testDropTable($schemaManager)
	{
		$td = $schemaManager->getTableDefinition('test_types');
		$sql = $schemaManager->dropTable($td);
		$this->assertEquals('DROP TABLE IF EXISTS `test_types`', $sql);
	}
}
