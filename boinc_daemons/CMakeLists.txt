find_package(MySQL REQUIRED)
find_package(BOINC REQUIRED)
find_package(OpenCV REQUIRED)

find_path(BOINC_INCLUDE_DIR config.h PATHS /boinc/src/boinc/ /boinc/)

include_directories(
    ${PROJECT_SOURCE_DIR}
    ${PROJECT_SOURCE_DIR}/undvc_common
	${BOINC_INCLUDE_DIR}
    ${BOINC_INCLUDE_DIR}/api
    ${BOINC_INCLUDE_DIR}/db
    ${BOINC_INCLUDE_DIR}/lib
    ${BOINC_INCLUDE_DIR}/sched
	${MYSQL_INCLUDE_DIR}
)

add_executable(wildlife_validator
    ${BOINC_INCLUDE_DIR}/sched/validator
    ${BOINC_INCLUDE_DIR}/sched/validate_util
    ${BOINC_INCLUDE_DIR}/sched/validate_util2
    wildlife_validation_policy)
target_link_libraries(wildlife_validator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES} ${OpenCV_LIBS})

add_executable(wildlife_assimilator
    ${BOINC_INCLUDE_DIR}/sched/assimilator
    ${BOINC_INCLUDE_DIR}/sched/validate_util
    wildlife_assimilation_policy)
target_link_libraries(wildlife_assimilator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES} ${Boost_LIBRARIES})

#find_package(CURL REQUIRED)
find_package(OpenSSL REQUIRED)

include_directories(
    ${Boost_INCLUDE_DIR}
    ${BOINC_INCLUDE_DIR}/tools/
)

add_executable(wildlife_work_generator wildlife_work_generator)
target_link_libraries(wildlife_work_generator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES} ${OPENSSL_LIBRARIES} ${Boost_LIBRARIES})

add_executable(crowd_observation_validator crowd_observation_validator)
target_link_libraries(crowd_observation_validator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES} ${OPENSSL_LIBRARIES})
