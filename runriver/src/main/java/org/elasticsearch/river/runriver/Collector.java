package org.elasticsearch.river.runriver;

//JAVA
import java.io.IOException;
import java.util.Map;
import java.util.List;
import java.util.HashMap;

//ELASTICSEARCH
import org.elasticsearch.client.Client;
import org.elasticsearch.action.get.GetResponse;
import org.elasticsearch.action.index.IndexResponse;
import org.elasticsearch.action.search.SearchResponse;
import org.elasticsearch.search.SearchHit;
import org.elasticsearch.search.SearchHits;
import org.elasticsearch.common.inject.Inject;

import org.elasticsearch.index.query.QueryBuilders;
import static org.elasticsearch.common.xcontent.XContentFactory.*;

//RIVER
import org.elasticsearch.river.River;
import org.elasticsearch.river.RiverName;
import org.elasticsearch.river.RiverSettings;

//JEST
import io.searchbox.client.JestClient;
//import io.searchbox.client.JestResult;

import io.searchbox.client.http.JestHttpClient;
import io.searchbox.client.JestClientFactory;
import io.searchbox.client.config.HttpClientConfig;
import io.searchbox.core.Search;
import io.searchbox.core.SearchResult;
import io.searchbox.indices.CreateIndex;

import io.searchbox.core.search.facet.HistogramFacet;
import io.searchbox.core.search.facet.TermsFacet;
import io.searchbox.core.search.facet.TermsStatsFacet;
import io.searchbox.core.search.facet.TermsStatsFacet.TermsStats;



//In java seems to be impossible to inherit/extend from multiple class
//so i need to implement the AbstractRiverComponent compatibility manually
//just to have the logger object. 
//https://github.com/elasticsearch/elasticsearch/blob/master/src/main/java/org/elasticsearch/river/AbstractRiverComponent.java

public class Collector extends AbstractRunRiverThread {

    private JestClient jestClient;
    Map<String, Integer> known_streams = new HashMap<String, Integer>();
    Map<String, Map<String, Double>> fuinlshist = new HashMap<String, Map<String, Double>>();
    Map<String, Map<String, Double>> fuoutlshist = new HashMap<String, Map<String, Double>>();
    Map<String, Map<String, Double>> fufilesizehist = new HashMap<String, Map<String, Double>>();


    Search search;
    SearchResult result;
    String res;
    Boolean EoR=false;
        
    @Inject
    public Collector(RiverName riverName, RiverSettings settings,Client client) {
        super(riverName,settings,client);
    }

    @Override
    public void beforeLoop(){
        logger.info("Collector started.");
        this.interval = fetching_interval;
        setJestClient();
    }
    @Override
    public void afterLoop(){
        logger.info("Collector stopped.");
    }

    @Override
    public void mainLoop() throws Exception {        
        logger.info("Collector running...");

        collectStates();
        collectStreams();
        checkRunEnd();
        checkBoxInfo();
    }


    public void collectStreams() throws Exception {
        //logger.info("COLLECT STREAMS");
        Boolean dataChanged;
        

        search = new Search.Builder(stream_count.toString())
            .addIndex("run"+String.format("%06d", Integer.parseInt(runNumber))+"*")
            .addType("fu-out").build();

        result = jestClient.execute(search);

        //logger.info("StreamCountQuery Index: run"+String.format("%06d", Integer.parseInt(runNumber))+"*");
        //logger.info("StreamCountQuery: "+stream_count.toString());
        //logger.info("StreamCountQuery result: "+result.getJsonString());
        
        List<TermsFacet> terms= result.getFacets(TermsFacet.class);
        if (terms.isEmpty()){logger.info("Stream_count empty");}
        else{
            for (TermsFacet.Term term : terms.get(0).terms()) {
                known_streams.put(term.getName(),term.getCount());
            }

            //logger.info("collectStreams: known streams "+known_streams.toString());
            for (String stream : known_streams.keySet()){
            
                fuinlshist.put(stream, new HashMap<String, Double>());
                fuoutlshist.put(stream, new HashMap<String, Double>());
                fufilesizehist.put(stream, new HashMap<String, Double>());



                stream_query.query(QueryBuilders.termQuery("stream",stream));

                search = new Search.Builder(stream_query.toString())
                .addIndex("run"+String.format("%06d", Integer.parseInt(runNumber))+"*")
                .addType("fu-out").build();
                result = jestClient.execute(search);
                List<TermsStatsFacet> termsStatsFacets = result.getFacets(TermsStatsFacet.class);

                //logger.info("StreamQuery Index: run"+String.format("%06d", Integer.parseInt(runNumber))+"*");
                //logger.info("StreamQuery: "+stream_query.toString());
                //logger.info("StreamQuery result: "+result.getJsonString());
                
                for (TermsStatsFacet termsStatsFacet : termsStatsFacets) {
                    String name = termsStatsFacet .getName();
                    List<TermsStats> termsStatsList = termsStatsFacet.getTermsStatsList();
                    for (TermsStats termsStats : termsStatsList){
                        String ls = termsStats.getTerm();
                        Double total = termsStats.getTotal();

                        if (name.equals("inls")){
                            fuinlshist.get(stream).put(ls,total);
                        }
                        if (name.equals("outls")){
                            fuoutlshist.get(stream).put(ls,total);
                        }
                        if (name.equals("filesize")){
                            fufilesizehist.get(stream).put(ls,total);
                        }
                    }

                } 
            }


            for (String stream : known_streams.keySet()){
                for (String ls : fuoutlshist.get(stream).keySet()){

                    String id = String.format("%06d", Integer.parseInt(runNumber))+stream+ls;


                    //Check if data is changed (to avoid to update timestamp if not necessary)
                    GetResponse sresponse = client.prepareGet(runIndex_write, "stream-hist", id)
                                                .setRouting(runNumber)
                                                .setRefresh(true).execute().actionGet();

                    dataChanged = true;
                    if (sresponse.isExists()){ 
                        Double in = Double.parseDouble(sresponse.getSource().get("in").toString());
                        Double out = Double.parseDouble(sresponse.getSource().get("out").toString());

                        if (   in.compareTo(fuinlshist.get(stream).get(ls))==0 
                            && out.compareTo(fuoutlshist.get(stream).get(ls))==0){
                            dataChanged = false;
                        } else { logger.info(id+" already exists and will be updated."); }
                    }
                    
                    //Update Data
                    if (dataChanged){
                        logger.info("stream-hist update for ls,stream: "+ls+","+stream+" in:"+fuinlshist.get(stream).get(ls).toString()+" out:"+fuoutlshist.get(stream).get(ls).toString());
                        IndexResponse response = client.prepareIndex(runIndex_write, "stream-hist").setRefresh(true)
                        .setParent(runNumber)
                        .setId(id)
                        .setSource(jsonBuilder()
                            .startObject()
                            .field("stream", stream)
                            .field("ls", Integer.parseInt(ls))
                            .field("in", fuinlshist.get(stream).get(ls))
                            .field("out", fuoutlshist.get(stream).get(ls))
                            .field("filesize", fufilesizehist.get(stream).get(ls))
                            .endObject())
                        .execute()
                        .actionGet();    
                    }
                    
                }

            }
        }
    }




    public void collectStates() throws Exception {
        logger.info("collectStates");
        search = new Search.Builder(state_query.toString())
            .addIndex("run"+String.format("%06d", Integer.parseInt(runNumber))+"*")
            .addType("prc-i-state").build();

        result = jestClient.execute(search);
        List<HistogramFacet> histogramFacets = result.getFacets(HistogramFacet.class);


        //res = String.valueOf(result.getJsonString());
        //logger.info(state_query.toString());
        //logger.info(result.getJsonString());
        if(histogramFacets.isEmpty()){logger.info("State query empty");}
        else if (histogramFacets.get(0).getHistograms().size() > 0 ){
            Long count = histogramFacets.get(0).getHistograms().get(0).getCount();
            if (count > 0){
                //logger.info("Create state-hist ");
                client.prepareIndex(runIndex_write, "state-hist")
                    .setParent(runNumber)
                    .setSource(result.getJsonObject().get("facets").toString())
                    .execute().actionGet();
                //logger.info(result.getJsonObject().get("facets").toString());
            //    for (HistogramFacet hist : histogramFacets) {
            //        logger.info(hist.getJsonString());
            //    }

            }
        }

        //logger.info(result.getJsonObject().get("facets").toString());
    }

    public void checkRunEnd(){
        if (EoR){return;}
        GetResponse response = client.prepareGet(runIndex_write, "run", runNumber).setRefresh(true).execute().actionGet();
        if (!response.isExists()){return;}
        if (response.getSource().get("endTime") != null){ logger.info("EoR received!"); EoR = true; }
    }

    public void checkBoxInfo(){
        if (!EoR){return;}
        SearchResponse response = client.search(boxinfo_query.indices(boxinfo_write)).actionGet();
        logger.info("Boxinfo: "+ String.valueOf(response.getHits().getTotalHits()));
        if (response.getHits().getTotalHits() == 0 ) { selfDelete(); }
    }



    public void setJestClient(){
         //JEST Configuration
         // Construct a new Jest client according to configuration via factory
         JestClientFactory factory = new JestClientFactory();
         factory.setHttpClientConfig(new HttpClientConfig
                                .Builder(es_tribe_host)
                                .multiThreaded(true)
                                .build());
         jestClient = factory.getObject();
    }
}
