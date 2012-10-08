package net.medcommons.mesa.action;



import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;

import org.apache.log4j.Logger;

import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mesa.MesaResultsManager;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;

/**
   * PDQ Partial Name Search
     * Test case 11315 covers a partial name search by the Patient 
     * Demographics Consumer. Several ADT messages are sent to the Patient 
     * Demographics Supplier. Then, the PD Consumer sends a query. The 
     * consumer is expected to query with a partial name:
     *     PID.5.1 = MOO*
     * No other query keys should be present.
     * If populated, field RCP-2 should contain the value 10^RD or a higher 
     * number. This will allow a response with all records. There are later 
     * tests for limiting the number of records in a response. QPD-1 (Message
     * Query Name) is a field that the Supplier needs to be able to configure
     * on a site specific basis. For these tests, set the value to
     * QRY_PDQ_1001^Query By Name^IHEDEMO
     * When querying a vendor system, you may need different wildcard 
     * characters (or no wildcard characters).
     * 
 * @author mesozoic
 *
 */
public class MesaPDC11315Action extends MesaBaseAction{

	private static Logger logger = Logger.getLogger(MesaPDC11315Action.class);
	 
	 public void executeTest() throws Exception{
		
	     setTestname("PDC 11315");
	     
	    OhfBridgeSoapBindingStub binding = null;
	      binding=createBinding();
	 
  
      OHFBridgeStub.PatientNameType nameType = new OHFBridgeStub.PatientNameType();
      nameType.setFamilyName("MOO*");
     
      
      OHFBridgeStub.PatientInfoType infoType = new OHFBridgeStub.PatientInfoType();
      infoType.setPatientName(nameType);
      
      RhioConfig[] fRhios = pdqResults.getFilteredRhios();
      
      OHFBridgeStub.PatientSearchPreferencesType patientSearchPreferencesType = new OHFBridgeStub.PatientSearchPreferencesType();
      OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
      assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1");
      assigningAuthorityType.setUniversalIdType("ISO");
      
      OHFBridgeStub.AssigningAuthorityType[] authorityTypes = new  OHFBridgeStub.AssigningAuthorityType[1];
      authorityTypes[0] = assigningAuthorityType;
      patientSearchPreferencesType.setDomainsReturned(authorityTypes);
      
     
      OHFBridgeStub.SearchPatientResponseType response = null;
      for(int i = 0; i < fRhios.length; i++){
          try{
          	System.out.println("Attempting to context RHIO " + fRhios[i].getName());
          	mesaResults.setRhioConfig(fRhios[i]);
          	SessionContext sessionContext = createSessionContext(fRhios[i]);
          	response = binding.searchPatient(sessionContext, infoType, patientSearchPreferencesType);
          	String failMessage = response.getFailMessage();
          	System.out.println("Failure message is " + failMessage);
          	PatientInfoType [] patients =response.getPatients();
          	mesaResults.setPatients(patients);
          	pdqResults.setPatientRecords(patients);
          
          	if (patients != null){
          		for (int j=0;j<patients.length; j++){
          			System.out.println("Response " + j);
          			System.out.println(patients[j].getPatientName().getFamilyName() + patients[j].getPatientName().getGivenName());
          			System.out.println(patients[j].getPatientDateOfBirth());
          			System.out.println(patients[j].getPatientIdentifier().getIdNumber() + "," + patients[j].getPatientIdentifier().getAssigningAuthorityType());
          		}
          	}
          	
          	String [] bridgeLog = binding.getMyLog(sessionContext, 0);
          	
          	String log = saveLog(bridgeLog, getLogFile());
          	logger.info(log);
          }
          catch(Exception e){
              e.printStackTrace(System.out);
          }
      }
  }
}
