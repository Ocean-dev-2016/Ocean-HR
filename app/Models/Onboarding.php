<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Onboarding extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'onboardings';

    protected $fillable = [
        'company_id',
        'employee_id',
        'onboarding_code',
        'first_name',
        'middle_name',
        'father_name',
        'last_name',
        'full_name',
        'email',
        'contact_number',
        'other_number',
        'date_of_birth',
        'gender',
        'blood_group',
        'marital_status',
        'current_address',
        'permanent_address',
        'branch_id',
        'department_id',
        'sub_department_id',
        'designation_id',
        'role_id',
        'shift_id',
        'reporting_manager_id',
        'buddy_id',
        'joining_date',
        'probation_period_months',
        'probation_end_date',
        'probation_status',
        'probation_notes',
        'employment_type',
        'job_description',
        'kra_kpi_details',
        'attendance_target_percentage',
        'late_mark_tolerance',
        'daily_working_hours_target',
        'jd_acknowledged',
        'jd_acknowledged_at',
        'status',
        'current_step',
        'progress_percentage',
        'company_overview_acknowledged',
        'company_overview_acknowledged_at',
        'company_overview_notes',
        'remarks',
        'completed_at',
        'completed_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'company_overview_acknowledged' => 'boolean',
        'company_overview_acknowledged_at' => 'datetime',
        'jd_acknowledged' => 'boolean',
        'jd_acknowledged_at' => 'datetime',
        'kra_kpi_details' => 'array',
        'completed_at' => 'datetime',
        'joining_date' => 'date',
        'probation_end_date' => 'date',
        'date_of_birth' => 'date',
    ];

    /**
     * Comprehensive predefined multi-industry role templates for Job Descriptions, KRAs & KPIs (One-Stop Solution)
     */
    public static function getRoleTemplates(): array
    {
        return [
            // ======================== 1. IT & SOFTWARE INDUSTRY ========================
            'developer' => [
                'key' => 'developer',
                'industry' => 'it_tech',
                'industry_label' => 'IT & Software',
                'industry_icon' => 'ti-device-laptop',
                'title' => 'Software Developer / Full Stack Engineer',
                'icon' => 'ti-code',
                'badge' => 'IT / Developer',
                'attendance_target_percentage' => 95,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for designing, developing, testing, deploying, and maintaining web/mobile applications, APIs, and database solutions.\n\nKey Responsibilities:\n1. Write clean, secure, modular, and maintainable code adhering to PSR, MVC architecture, and coding standards.\n2. Develop robust RESTful APIs, integrate third-party payment/auth SDKs, and build responsive frontend user interfaces.\n3. Participate in sprint planning, perform peer code reviews (PRs), and debug production issues efficiently.\n4. Optimize database queries, indexing, application caching (Redis), and system scalability.\n5. Maintain Git branch workflows, CI/CD deployment pipelines, and write comprehensive unit/feature tests.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Feature Development & Sprint Delivery',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'On-time sprint ticket / feature completion', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Code quality & PR review compliance (clean code)', 'target' => '>= 90%', 'frequency' => 'Monthly'],
                            ['metric' => 'Critical production bug count post-release', 'target' => '0 critical defects', 'frequency' => 'Per Release'],
                        ]
                    ],
                    [
                        'kra_title' => 'System Architecture, API & Database Optimization',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'API response time & query optimization', 'target' => '< 200ms avg', 'frequency' => 'Monthly'],
                            ['metric' => 'Data security & adherence to vulnerability guidelines', 'target' => '100% compliant', 'frequency' => 'Continuous'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Punctuality & Shift Discipline',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality & Grace Period adherence', 'target' => '<= 2 late marks/mo', 'frequency' => 'Monthly'],
                            ['metric' => 'Daily Git commits & ERP work logging', 'target' => '100% daily logging', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Agile Collaboration, R&D & Tech Upgrades',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Active participation in daily scrums & sprint retros', 'target' => '100% participation', 'frequency' => 'Weekly'],
                            ['metric' => 'Knowledge sharing & R&D contribution', 'target' => '1 session / quarter', 'frequency' => 'Quarterly'],
                        ]
                    ],
                ]
            ],
            'ui_ux' => [
                'key' => 'ui_ux',
                'industry' => 'it_tech',
                'industry_label' => 'IT & Software',
                'industry_icon' => 'ti-device-laptop',
                'title' => 'UI / UX Designer & Product Designer',
                'icon' => 'ti-palette',
                'badge' => 'UI / UX Design',
                'attendance_target_percentage' => 95,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for conceptualizing, designing, and delivering intuitive, user-friendly UI layouts, wireframes, high-fidelity prototypes, and cohesive Design Systems across web and mobile platforms.\n\nKey Responsibilities:\n1. Conduct user research, user journey mapping, information architecture, and competitive UI/UX audits.\n2. Design pixel-perfect web and mobile screen mockups, interactive prototypes using Figma and Adobe Creative Suite.\n3. Create, maintain, and expand company Design Systems, style guides, reusable components, and icon libraries.\n4. Collaborate closely with product managers and frontend developers for flawless design handoff and QA review.\n5. Conduct usability testing sessions, collect customer feedback, and iterate designs to maximize conversion and UX satisfaction.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'UI/UX Asset Delivery & Wireframe Milestones',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'On-time delivery of Figma screens and UI flows', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Design system component consistency & reusability', 'target' => '100% compliant', 'frequency' => 'Monthly'],
                            ['metric' => 'Developer design-handoff satisfaction score', 'target' => '>= 9.0 / 10', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'User Experience Quality & Usability Optimization',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Usability test success rate & navigation clarity', 'target' => '>= 90%', 'frequency' => 'Monthly'],
                            ['metric' => 'Post-release UI defect reports & design misalignment', 'target' => '< 2 minor issues', 'frequency' => 'Per Release'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Punctuality & Timeline Adherence',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality & Grace Late Marks', 'target' => '<= 2 late marks/mo', 'frequency' => 'Monthly'],
                            ['metric' => 'Design sprint roadmap commitment fulfillment', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Creative Innovation & Design Trends Exploration',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Micro-animations & interactive prototype demonstrations', 'target' => 'Consistent', 'frequency' => 'Monthly'],
                            ['metric' => 'Benchmarking modern UX trends & presentation to team', 'target' => '1 workshop / quarter', 'frequency' => 'Quarterly'],
                        ]
                    ],
                ]
            ],
            'qa_tester' => [
                'key' => 'qa_tester',
                'industry' => 'it_tech',
                'industry_label' => 'IT & Software',
                'industry_icon' => 'ti-device-laptop',
                'title' => 'QA / Software Tester & Automation',
                'icon' => 'ti-bug',
                'badge' => 'Quality Assurance',
                'attendance_target_percentage' => 95,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for guaranteeing software stability, functionality, security, and performance through rigorous manual and automated test execution across software releases.\n\nKey Responsibilities:\n1. Analyze software specifications and design detailed test scenarios, test cases, and traceability matrices.\n2. Perform functional, regression, sanity, cross-browser, responsive, and REST API testing (using Postman).\n3. Develop and maintain automated test scripts (Playwright / Cypress / Selenium) within CI/CD pipelines.\n4. Log, categorize, prioritize, and track bugs in bug tracking systems with reproducible steps and logs.\n5. Collaborate with developers to verify fixes and ensure zero critical/blocker bug leakage into production.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Test Coverage & Bug Identification Precision',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'Test case execution coverage across release features', 'target' => '100% coverage', 'frequency' => 'Monthly'],
                            ['metric' => 'Defect detection rate before UAT / Production release', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Production critical bug leakage rate', 'target' => '< 1%', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Automation Testing & API Validation',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Regression test automation coverage percentage', 'target' => '>= 60%', 'frequency' => 'Quarterly'],
                            ['metric' => 'API endpoint test verification and security checks', 'target' => '100% validated', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Punctuality & Release Timeline Delivery',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality adherence & grace tolerance', 'target' => '<= 2 late marks/mo', 'frequency' => 'Monthly'],
                            ['metric' => 'Timely QA sign-off before scheduled client releases', 'target' => '100% on-time', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'QA Documentation & Process Excellence',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Test case repository freshness & bug report clarity', 'target' => '100% compliant', 'frequency' => 'Monthly'],
                            ['metric' => 'Defect density analysis & QA metrics reporting', 'target' => 'Monthly report', 'frequency' => 'Monthly'],
                        ]
                    ],
                ]
            ],
            'devops_it' => [
                'key' => 'devops_it',
                'industry' => 'it_tech',
                'industry_label' => 'IT & Software',
                'industry_icon' => 'ti-device-laptop',
                'title' => 'DevOps Engineer & IT Support Specialist',
                'icon' => 'ti-server-2',
                'badge' => 'DevOps / IT Admin',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for server infrastructure, cloud deployments (AWS/DigitalOcean/Linux), CI/CD automation pipelines, network security, SSL certificates, backup management, and office IT support.\n\nKey Responsibilities:\n1. Maintain server uptime, load balancers, database replication, and automated daily backups.\n2. Configure and monitor CI/CD deployment pipelines (GitHub Actions, Docker, Jenkins).\n3. Manage internal office network, firewall, VPN, domain emails, and software license assets.\n4. Perform vulnerability assessments, security patching, and server log monitoring.\n5. Provide rapid Tier-1/2 IT support to internal team members for hardware/software issues.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Server Uptime & Infrastructure Reliability',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'Cloud server & application uptime SLA', 'target' => '>= 99.9% uptime', 'frequency' => 'Monthly'],
                            ['metric' => 'Automated backup success & verification test', 'target' => '100% verified', 'frequency' => 'Weekly'],
                            ['metric' => 'Security incident / vulnerability breach count', 'target' => '0 incidents', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'CI/CD Pipeline Automation & Deployment Speed',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Deployment pipeline execution success rate', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Production rollback and disaster recovery readiness', 'target' => '< 15 mins RTO', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Shift Punctuality & Emergency Response',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality adherence & on-call emergency response', 'target' => '< 10 mins ack', 'frequency' => 'Continuous'],
                        ]
                    ],
                    [
                        'kra_title' => 'Internal IT Support SLA & Asset Maintenance',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Office IT ticket resolution turnaround SLA', 'target' => '<= 2 hours avg', 'frequency' => 'Monthly'],
                            ['metric' => 'Hardware asset register & software audit accuracy', 'target' => '100% updated', 'frequency' => 'Quarterly'],
                        ]
                    ],
                ]
            ],
            'project_manager' => [
                'key' => 'project_manager',
                'industry' => 'it_tech',
                'industry_label' => 'IT & Software',
                'industry_icon' => 'ti-device-laptop',
                'title' => 'Technical Project Manager / Scrum Master',
                'icon' => 'ti-clipboard-list',
                'badge' => 'Project Management',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for end-to-end software project planning, agile sprint facilitation, scope management, milestone delivery, client communication, and team productivity tracking.\n\nKey Responsibilities:\n1. Facilitate agile scrums, sprint planning, backlog grooming, and retrospective meetings.\n2. Define sprint goals, track burndown charts, identify blockers, and ensure cross-team alignment.\n3. Act as primary technical liaison between clients, management, design, and engineering teams.\n4. Monitor project budgets, billable hours, scope changes, and change-request authorizations.\n5. Prepare weekly milestone progress reports, risk mitigation registers, and executive dashboards.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Project Milestone Delivery & Sprint Governance',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'Project milestone completion on scheduled deadline', 'target' => '>= 95% on-time', 'frequency' => 'Monthly'],
                            ['metric' => 'Sprint velocity & commitment fulfillment ratio', 'target' => '>= 90%', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Client Communication & Stakeholder Satisfaction',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Client satisfaction index (CSAT) rating', 'target' => '>= 9.0 / 10', 'frequency' => 'Quarterly'],
                            ['metric' => 'Weekly status reporting & demo delivery consistency', 'target' => '100% on schedule', 'frequency' => 'Weekly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Executive Punctuality & Leadership',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Meeting punctuality & scrum facilitation discipline', 'target' => '100% punctual', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Resource Utilization & Scope Control',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Billable engineering resource utilization rate', 'target' => '>= 85%', 'frequency' => 'Monthly'],
                            ['metric' => 'Scope creep prevention & change-order approvals', 'target' => '100% documented', 'frequency' => 'Monthly'],
                        ]
                    ],
                ]
            ],

            // ======================== 2. INDUSTRIAL & MANUFACTURING (WHITE / GREY / BLUE COLLAR) ========================
            'ceo_director' => [
                'key' => 'ceo_director',
                'industry' => 'white_collar',
                'industry_label' => 'White Collar (Executive)',
                'industry_icon' => 'ti-crown',
                'title' => 'CEO / Managing Director / Plant Head',
                'icon' => 'ti-crown',
                'badge' => 'White Collar / Executive',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 9.00,
                'job_description' => "Role Overview: Responsible for the entire organizational leadership, company strategic vision, business expansion, plant profitability, capital investments, corporate governance, and top-level client/investor relationships.\n\nKey Responsibilities:\n1. Formulate and execute long-term strategic plans to expand market share, revenue, and EBITDA margins.\n2. Oversee executive leaders across Production, HR, Finance, Engineering, Quality, and Sales.\n3. Monitor overall operational performance, plant throughput, P&L statements, and annual Capex budgets.\n4. Drive innovation, automation, modern ERP adoption, and continuous operational excellence across all plants.\n5. Represent the enterprise in key stakeholder forums, industry associations, and major client contracts.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Enterprise Revenue, Profitability & P&L Health',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'Annual organizational turnover & revenue growth target', 'target' => '>= 100% target', 'frequency' => 'Quarterly'],
                            ['metric' => 'EBITDA margin percentage & net profitability target', 'target' => 'Target achieved', 'frequency' => 'Quarterly'],
                            ['metric' => 'Operating cash flow & working capital cycle optimization', 'target' => 'Healthy cycle', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Plant Productivity, Operational Scale & Capex ROI',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Overall plant production throughput vs capacity', 'target' => '>= 95% capacity', 'frequency' => 'Monthly'],
                            ['metric' => 'On-time delivery of major expansion & Capex projects', 'target' => '100% on-time', 'frequency' => 'Quarterly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Executive Governance, Leadership & Culture',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Executive attendance, board meetings & monthly reviews', 'target' => '100% attendance', 'frequency' => 'Monthly'],
                            ['metric' => 'Key leadership talent retention and succession readiness', 'target' => '>= 95% retention', 'frequency' => 'Annual'],
                        ]
                    ],
                    [
                        'kra_title' => 'Statutory Compliance, CSR & Corporate Reputation',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Zero legal, safety or major regulatory compliance breach', 'target' => '100% compliant', 'frequency' => 'Continuous'],
                            ['metric' => 'Enterprise client CSAT & brand NPS index', 'target' => '>= 9.0 / 10', 'frequency' => 'Quarterly'],
                        ]
                    ],
                ]
            ],
            'main_hr' => [
                'key' => 'main_hr',
                'industry' => 'white_collar',
                'industry_label' => 'White Collar (HR & Admin)',
                'industry_icon' => 'ti-users-group',
                'title' => 'Main HR / Senior HR & Admin Head',
                'icon' => 'ti-users-group',
                'badge' => 'White Collar / HR Head',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for leading the company-wide Human Resource and Administration function, talent acquisition, worker/staff onboarding, industrial labor relations, statutory factory compliances, monthly payroll execution, and employee welfare.\n\nKey Responsibilities:\n1. Spearhead recruitment for White, Grey, and Blue-collar positions, reducing time-to-hire across departments.\n2. Ensure 100% statutory labor compliance under Factory Act, PF, ESIC, Gratuity, Bonus, and Minimum Wages.\n3. Supervise flawless biometric attendance tracking, shift scheduling, leave policies, and monthly payroll processing.\n4. Drive employee engagement, grievance redressal, conflict resolution, and shopfloor labor harmony.\n5. Plan and oversee employee onboarding, skill development, safety induction, and annual performance appraisal cycles.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Talent Acquisition & Headcount Fulfillment SLA',
                        'weightage' => 35,
                        'kpis' => [
                            ['metric' => 'Manpower requisition fulfillment on-time rate', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Average time-to-hire turnaround for key roles', 'target' => '<= 15 days', 'frequency' => 'Monthly'],
                            ['metric' => 'Cost-per-hire optimization vs allocated budget', 'target' => 'Within budget', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Statutory Compliance, Labor Laws & Payroll Accuracy',
                        'weightage' => 30,
                        'kpis' => [
                            ['metric' => '100% PF, ESIC, Factory Act & statutory challan filings', 'target' => '100% on-time', 'frequency' => 'Monthly'],
                            ['metric' => 'Monthly payroll processing & salary disbursement accuracy', 'target' => 'Zero errors, by 7th', 'frequency' => 'Monthly'],
                            ['metric' => 'Labor audit & inspection readiness score', 'target' => '100% compliant', 'frequency' => 'Continuous'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Professional Punctuality & Self Discipline',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality adherence & grace arrival tolerance', 'target' => '<= 1 late mark/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Employee Retention, Welfare & Training Induction',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Staff and worker retention / turnover rate', 'target' => '< 5% attrition', 'frequency' => 'Monthly'],
                            ['metric' => '100% completion of new joinee onboarding & induction', 'target' => '100% complete', 'frequency' => 'Monthly'],
                        ]
                    ],
                ]
            ],
            'plant_supervisor' => [
                'key' => 'plant_supervisor',
                'industry' => 'grey_collar',
                'industry_label' => 'Grey Collar (Supervisor)',
                'industry_icon' => 'ti-user-check',
                'title' => 'Supervisor / Production & Shopfloor Incharge',
                'icon' => 'ti-user-check',
                'badge' => 'Grey Collar / Supervisor',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 9.00,
                'job_description' => "Role Overview: Responsible for direct on-ground supervision of shift operations, worker allocation, line piece-rate balancing, monitoring hourly machine output, troubleshooting line bottlenecks, scrap reduction, and enforcing 5S and shopfloor safety.\n\nKey Responsibilities:\n1. Conduct daily shift roll-call, allocate operators/workers to machines and assembly stations.\n2. Monitor hourly production progress against shift target sheet and resolve line stoppage immediately.\n3. Guide machine operators and helpers on standard operating procedures (SOPs), cycle times, and quality criteria.\n4. Ensure strict shopfloor discipline, timely tea/lunch breaks, zero idle-time, and shift handover documentation.\n5. Enforce 100% safety shoe, helmet, glove, and PPE compliance on the shopfloor.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Shift Production Target & Hourly Line Output',
                        'weightage' => 45,
                        'kpis' => [
                            ['metric' => 'Shift target piece production volume achievement', 'target' => '>= 98% target', 'frequency' => 'Daily'],
                            ['metric' => 'Line balancing & reduction in machine downtime / idle time', 'target' => '< 15 mins/shift', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Worker Allocation, Line Scrap & Quality Control',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Shift component rejection & rework percentage', 'target' => '< 1.5%', 'frequency' => 'Daily'],
                            ['metric' => 'Optimal worker allocation & line productivity index', 'target' => '>= 95% efficiency', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Shift Attendance, Punctuality & Handover Discipline',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Self Attendance and shift start punctuality', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Worker shift attendance tracking & leave management', 'target' => '100% logged', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => '5S Workplace Housekeeping & PPE Safety Enforcement',
                        'weightage' => 10,
                        'kpis' => [
                            ['metric' => 'Daily 5S cleaning and machine housekeeping check', 'target' => '100% daily check', 'frequency' => 'Daily'],
                            ['metric' => '100% worker PPE compliance and zero unsafe acts', 'target' => '100% safe', 'frequency' => 'Continuous'],
                        ]
                    ],
                ]
            ],
            'plant_worker' => [
                'key' => 'plant_worker',
                'industry' => 'blue_collar',
                'industry_label' => 'Blue Collar (Worker)',
                'industry_icon' => 'ti-tools',
                'title' => 'Plant Worker / Machine Operator & Assembly Labor',
                'icon' => 'ti-tools',
                'badge' => 'Blue Collar / Worker',
                'attendance_target_percentage' => 96,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 9.00,
                'job_description' => "Role Overview: Responsible for performing assigned manufacturing, manual machine operation, parts assembly, packing, material handling, loading/unloading, basic visual quality checks, and maintaining clean workstation.\n\nKey Responsibilities:\n1. Execute assigned daily assembly, manufacturing, packing, or material handling duties as directed by supervisor.\n2. Operate allotted machine or tools strictly adhering to safety guidelines, guards, and operating instructions.\n3. Inspect finished pieces visually for scratches, burrs, dents, or defects before passing to the next station.\n4. Wear all mandatory Personal Protective Equipment (PPE) like safety shoes, goggles, gloves, and aprons.\n5. Keep workstation, tools, and surrounding floor area clean, organized, and free from oil/scrap.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Daily Piece Production Quota & Assembly Speed',
                        'weightage' => 50,
                        'kpis' => [
                            ['metric' => 'Daily component production / assembly quota completion', 'target' => '100% daily target', 'frequency' => 'Daily'],
                            ['metric' => 'Standard cycle time compliance per component', 'target' => 'On target', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Workmanship Quality & Defect-Free Handling',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Zero manual damage, denting or careless rejection', 'target' => '< 1% scrap', 'frequency' => 'Daily'],
                            ['metric' => 'Careful handling of raw materials, parts, and tooling', 'target' => '100% proper', 'frequency' => 'Continuous'],
                        ]
                    ],
                    [
                        'kra_title' => 'Shift Attendance, On-time Biometric Punching',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 96%', 'frequency' => 'Monthly'],
                            ['metric' => 'Shift punch-in punctuality (no late reporting)', 'target' => '<= 2 late marks/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Mandatory PPE Safety & Workstation Cleanliness',
                        'weightage' => 10,
                        'kpis' => [
                            ['metric' => 'Mandatory wearing of Safety Shoes & PPE at all times', 'target' => '100% compliant', 'frequency' => 'Daily'],
                            ['metric' => 'Daily end-of-shift workstation cleaning and tool return', 'target' => '100% daily', 'frequency' => 'Daily'],
                        ]
                    ],
                ]
            ],
            'production_manager' => [
                'key' => 'production_manager',
                'industry' => 'industrial',
                'industry_label' => 'Industrial & Manufacturing',
                'industry_icon' => 'ti-building-factory-2',
                'title' => 'Production & Plant Incharge / Manager',
                'icon' => 'ti-building-factory',
                'badge' => 'Manufacturing / Plant',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 9.00,
                'job_description' => "Role Overview: Responsible for overseeing end-to-end plant production operations, shift planning, machine capacity utilization, shopfloor discipline, raw material consumption, and timely dispatch targets.\n\nKey Responsibilities:\n1. Plan daily and monthly production schedules (PPC) based on sales orders and delivery commitments.\n2. Manage shopfloor supervisors, machine operators, helpers, and maintain shift discipline.\n3. Minimize production cycle time, machine downtime, raw material wastage, and scrap generation.\n4. Enforce plant safety norms, 5S workplace organization, PPE adherence, and ISO compliance.\n5. Coordinate with maintenance, quality inspection, and store departments for smooth material flow.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Production Target Fulfillment & Output Volume',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'Daily & monthly production quota achievement', 'target' => '>= 98% vs plan', 'frequency' => 'Monthly'],
                            ['metric' => 'On-time production order completion for dispatch', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Wastage Reduction, Scrap Control & Machine OEE',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Overall Equipment Effectiveness (OEE) rate', 'target' => '>= 85% OEE', 'frequency' => 'Monthly'],
                            ['metric' => 'Raw material scrap / rejection percentage', 'target' => '< 1.5%', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Plant Attendance, Shift Discipline & Punctuality',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Self Attendance and shift handover punctuality', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Shopfloor worker attendance & overtime control', 'target' => '>= 95% line presence', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Shopfloor Safety, 5S & Equipment Maintenance',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Lost Time Injury (LTI) / Zero safety accidents', 'target' => '0 safety incidents', 'frequency' => 'Monthly'],
                            ['metric' => '5S audit rating across plant shopfloor zones', 'target' => '>= 90% score', 'frequency' => 'Monthly'],
                        ]
                    ],
                ]
            ],
            'cnc_operator' => [
                'key' => 'cnc_operator',
                'industry' => 'industrial',
                'industry_label' => 'Industrial & Manufacturing',
                'industry_icon' => 'ti-building-factory-2',
                'title' => 'CNC / VMC Machine Operator & Technician',
                'icon' => 'ti-cpu',
                'badge' => 'CNC / Machine Operator',
                'attendance_target_percentage' => 96,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 9.00,
                'job_description' => "Role Overview: Responsible for setup, programming, tooling, component loading, and operation of CNC / VMC / Lathe machines to produce high-precision industrial components as per engineering drawings.\n\nKey Responsibilities:\n1. Read technical engineering blueprints, GD&T tolerance specs, and set up machine tool offsets.\n2. Operate CNC/VMC/Lathe machines efficiently to achieve targeted hourly piece output.\n3. Inspect first-piece and in-process dimensions using vernier calipers, micrometers, and bore gauges.\n4. Perform daily machine lubrication (TPM), coolant top-up, chip cleaning, and tooling life monitoring.\n5. Comply strictly with shopfloor safety protocols, ear/eye protection, and safety guards.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Shift Piece Production & Hourly Output',
                        'weightage' => 45,
                        'kpis' => [
                            ['metric' => 'Shift job piece production quota achievement', 'target' => '>= 100% quota', 'frequency' => 'Daily'],
                            ['metric' => 'Cycle time adherence per component drawing', 'target' => 'Target achieved', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Machining Dimensional Accuracy & Low Rejection',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'First-piece & in-process machining rejection rate', 'target' => '< 1.0%', 'frequency' => 'Monthly'],
                            ['metric' => 'Tool breakage & insert consumption control', 'target' => 'Within standard budget', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Shift Attendance, Punch-in Punctuality & Discipline',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 96%', 'frequency' => 'Monthly'],
                            ['metric' => 'Shift punch-in punctuality (no shift delay)', 'target' => '<= 2 late marks/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Daily Machine TPM & Safety Compliance',
                        'weightage' => 10,
                        'kpis' => [
                            ['metric' => 'Daily machine cleaning, oiling & maintenance check', 'target' => '100% daily check', 'frequency' => 'Daily'],
                            ['metric' => 'Mandatory PPE (Safety Shoes, Goggles) compliance', 'target' => '100% compliant', 'frequency' => 'Continuous'],
                        ]
                    ],
                ]
            ],
            'qc_inspector' => [
                'key' => 'qc_inspector',
                'industry' => 'industrial',
                'industry_label' => 'Industrial & Manufacturing',
                'industry_icon' => 'ti-building-factory-2',
                'title' => 'Quality Control (QC / QA) Inspector & Safety Officer',
                'icon' => 'ti-shield-check',
                'badge' => 'Quality & Safety',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 9.00,
                'job_description' => "Role Overview: Responsible for incoming raw material inspection, in-process stage quality gates, final pre-dispatch inspection (PDI), calibration of measuring instruments, and shopfloor safety enforcement.\n\nKey Responsibilities:\n1. Perform receiving inspection of raw materials, vendor parts, and verify MTC (Mill Test Certificates).\n2. Conduct regular line audits, stage inspections, surface finish, hardness, and tolerance verification.\n3. Prepare Quality Assurance Plans (QAP), Inspection Reports (IR), and manage Non-Conformance Reports (NCR).\n4. Maintain calibration schedules for all gauges, micrometers, calipers, and testing instruments.\n5. Investigate customer quality complaints, perform 8D/Root Cause Analysis (RCA), and implement CAPA.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Quality Gate Inspection & Defect Prevention',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'Incoming and in-process inspection turnaround time', 'target' => '<= 2 hours', 'frequency' => 'Daily'],
                            ['metric' => 'Customer rejection / RMA complaint leakage rate', 'target' => '0 customer returns', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'CAPA Implementation & Gauge Calibration',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'NCR resolution and CAPA closure turnaround SLA', 'target' => '<= 5 working days', 'frequency' => 'Monthly'],
                            ['metric' => 'Gauge & instrument calibration compliance', 'target' => '100% on schedule', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Shift Punctuality & Inspection Audits',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Shift inspection punctuality & gate clearance', 'target' => '100% on-time', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Safety Audits & Quality Documentation',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Daily inspection report logging & ISO compliance', 'target' => '100% logged daily', 'frequency' => 'Daily'],
                            ['metric' => 'Shopfloor safety hazard identification & reporting', 'target' => '>= 4 reports/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                ]
            ],
            'maintenance_eng' => [
                'key' => 'maintenance_eng',
                'industry' => 'industrial',
                'industry_label' => 'Industrial & Manufacturing',
                'industry_icon' => 'ti-building-factory-2',
                'title' => 'Plant Maintenance & Electrical Engineer',
                'icon' => 'ti-tool',
                'badge' => 'Plant Maintenance',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 9.00,
                'job_description' => "Role Overview: Responsible for preventive, predictive, and breakdown maintenance of all production machinery, electrical panels, hydraulic/pneumatic systems, compressors, and plant utility infrastructure.\n\nKey Responsibilities:\n1. Execute scheduled preventive maintenance (PM) checklists across all plant machinery and CNC lines.\n2. Troubleshoot and rapidly rectify electrical, PLC, mechanical, and hydraulic machine breakdowns.\n3. Manage spare parts inventory (bearings, seals, relays, fuses) to prevent prolonged machine outages.\n4. Monitor power factor, DG set operations, air compressor pressure, and utility power consumption.\n5. Enforce Lockout-Tagout (LOTO) procedures during electrical repairs and heavy machine maintenance.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Machine Breakdown MTTR & Plant Downtime Control',
                        'weightage' => 45,
                        'kpis' => [
                            ['metric' => 'Mean Time To Repair (MTTR) during machine breakdown', 'target' => '< 45 mins avg', 'frequency' => 'Monthly'],
                            ['metric' => 'Total plant unplanned machine downtime rate', 'target' => '< 2.0% of total hrs', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Preventive Maintenance (PM) Schedule Compliance',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Monthly preventive maintenance (PM) task completion', 'target' => '100% on schedule', 'frequency' => 'Monthly'],
                            ['metric' => 'Critical spare parts availability in inventory', 'target' => '>= 98% stock', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Emergency Breakdown Response & Punctuality',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Emergency breakdown attendance & shift punctuality', 'target' => 'Immediate response', 'frequency' => 'Continuous'],
                        ]
                    ],
                    [
                        'kra_title' => 'Utility Optimization & Electrical Safety (LOTO)',
                        'weightage' => 10,
                        'kpis' => [
                            ['metric' => 'Power factor maintenance & energy audit compliance', 'target' => '>= 0.98 PF', 'frequency' => 'Monthly'],
                            ['metric' => 'LOTO safety checklist compliance during repairs', 'target' => '100% compliant', 'frequency' => 'Continuous'],
                        ]
                    ],
                ]
            ],
            'store_inventory' => [
                'key' => 'store_inventory',
                'industry' => 'industrial',
                'industry_label' => 'Industrial & Manufacturing',
                'industry_icon' => 'ti-building-factory-2',
                'title' => 'Warehouse, Store & Raw Material Incharge',
                'icon' => 'ti-packages',
                'badge' => 'Store & Inventory',
                'attendance_target_percentage' => 97,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 9.00,
                'job_description' => "Role Overview: Responsible for managing raw material receiving, inward GRN generation, material issuance against job orders (FIFO), bin-card updates, physical stock verification, and warehouse dispatch readiness.\n\nKey Responsibilities:\n1. Inspect delivery challans, generate Good Receipt Notes (GRN), and update ERP inventory in real time.\n2. Issue materials, tools, and consumables to production lines against verified material requisition slips.\n3. Maintain FIFO (First In First Out) storage discipline, bin numbering, and scrap segregation.\n4. Conduct weekly and monthly physical stock audits and reconcile inventory discrepancies.\n5. Coordinate with logistics and dispatch teams for packaging, palletizing, and loading finished goods.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Inventory Accuracy & Physical Stock Reconciliation',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'Physical stock vs ERP inventory accuracy rate', 'target' => '>= 99.5%', 'frequency' => 'Monthly'],
                            ['metric' => 'Inward GRN entry turnaround time upon truck arrival', 'target' => '<= 4 hours', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Material Issue Turnaround & FIFO Adherence',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Shopfloor requisition material issue turnaround SLA', 'target' => '<= 30 minutes', 'frequency' => 'Daily'],
                            ['metric' => 'FIFO stock rotation compliance & dead-stock alerts', 'target' => '100% compliant', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Shift Punctuality & Store Discipline',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 97%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality adherence & store opening discipline', 'target' => '<= 2 late marks/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Warehouse 5S, Safe Stacking & Packaging Quality',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Warehouse 5S audit rating & bin cleanliness', 'target' => '>= 90%', 'frequency' => 'Monthly'],
                            ['metric' => 'Zero material transit damage due to packing defects', 'target' => '0 damage cases', 'frequency' => 'Monthly'],
                        ]
                    ],
                ]
            ],

            // ======================== 3. ACCOUNTS, FINANCE & TAXATION ========================
            'accountant' => [
                'key' => 'accountant',
                'industry' => 'accounts',
                'industry_label' => 'Accounts & Finance',
                'industry_icon' => 'ti-receipt-tax',
                'title' => 'Senior Accountant & Tax Executive (GST, TDS & Tally)',
                'icon' => 'ti-calculator',
                'badge' => 'Accounts & Taxation',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for managing day-to-day book-keeping, purchase/sales entry, GST returns (GSTR-1/3B), TDS computations, bank reconciliation (BRS), debtor/creditor follow-ups, and monthly balance sheet preparation.\n\nKey Responsibilities:\n1. Maintain accurate accounting vouchers (Sales, Purchase, Journal, Bank/Cash) in Tally/Busy/ERP.\n2. Prepare and file monthly GST returns (GSTR-1, GSTR-3B), reconcile 2B vs purchase ledgers.\n3. Compute monthly TDS deductions, generate challans, and assist in quarterly TDS return filings.\n4. Perform daily/weekly Bank Reconciliation Statements (BRS) and petty cash audits.\n5. Follow up on outstanding Accounts Receivable (Debtors aging) and prepare vendor payout schedules.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Statutory Compliance & GST/TDS Filing Precision',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'Timely filing of GST & TDS returns before statutory due date', 'target' => '100% on-time', 'frequency' => 'Monthly'],
                            ['metric' => 'GSTR-2B input tax credit (ITC) reconciliation accuracy', 'target' => '100% error-free', 'frequency' => 'Monthly'],
                            ['metric' => 'Zero late fee / penalty incurred on statutory filings', 'target' => '0 penalty', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Bank Reconciliation & Ledger Accuracy',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Bank Reconciliation Statement (BRS) completion', 'target' => 'Daily / Weekly', 'frequency' => 'Weekly'],
                            ['metric' => 'Debtor aging follow-up & DSO (Days Sales Outstanding)', 'target' => '<= 45 days DSO', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Punctuality & Financial Audit Reviews',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality adherence & grace tolerance', 'target' => '<= 1 late mark/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Monthly MIS Reporting & Financial Closing',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Monthly P&L, balance sheet and cashflow submission', 'target' => 'By 5th of month', 'frequency' => 'Monthly'],
                            ['metric' => 'Internal & external CA audit query clearance', 'target' => '100% resolved', 'frequency' => 'Quarterly'],
                        ]
                    ],
                ]
            ],
            'billing_executive' => [
                'key' => 'billing_executive',
                'industry' => 'accounts',
                'industry_label' => 'Accounts & Finance',
                'industry_icon' => 'ti-receipt-tax',
                'title' => 'Billing, Invoicing & E-Way Bill Executive',
                'icon' => 'ti-file-invoice',
                'badge' => 'Billing & Invoicing',
                'attendance_target_percentage' => 97,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for generating accurate sales tax invoices, E-Way bills, E-Invoices on GST portal, cross-checking purchase orders, pricing, HSN codes, tax rates, and coordinating dispatch billing documents.\n\nKey Responsibilities:\n1. Generate GST tax invoices, proforma invoices, and credit/debit notes as per approved purchase orders.\n2. Generate E-Way bills and IRN E-Invoices without delays to ensure hassle-free goods transportation.\n3. Verify customer billing addresses, GSTIN numbers, state codes, HSN tax brackets, and transport details.\n4. Reconcile daily dispatch reports with billed invoices and maintain physical/digital invoice copy registers.\n5. Coordinate with sales, dispatch, and transporter representatives to rectify billing errors swiftly.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Invoice Generation Velocity & Error-Free Billing',
                        'weightage' => 45,
                        'kpis' => [
                            ['metric' => 'Invoice & E-Way bill generation turnaround time', 'target' => '<= 15 mins/order', 'frequency' => 'Daily'],
                            ['metric' => 'Billing error rate (wrong GSTIN, price, HSN discrepancy)', 'target' => '< 0.5%', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'E-Way Bill Compliance & Transport Documentation',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'E-Way bill expiry monitoring and extension compliance', 'target' => '100% compliant', 'frequency' => 'Daily'],
                            ['metric' => 'Delivery challan to tax invoice reconciliation', 'target' => '100% daily matched', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Shift Punctuality & Dispatch Support',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 97%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality adherence during dispatch billing hours', 'target' => '<= 2 late marks/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Customer Billing Ledger Reconciliation',
                        'weightage' => 10,
                        'kpis' => [
                            ['metric' => 'Resolving client billing discrepancies & credit note turnaround', 'target' => '<= 24 hours', 'frequency' => 'Monthly'],
                            ['metric' => 'Monthly invoice register audit & filing compliance', 'target' => '100% filed', 'frequency' => 'Monthly'],
                        ]
                    ],
                ]
            ],

            // ======================== 4. HR, ADMIN & OPERATIONS ========================
            'hr_executive' => [
                'key' => 'hr_executive',
                'industry' => 'hr_admin',
                'industry_label' => 'HR, Admin & Operations',
                'industry_icon' => 'ti-users',
                'title' => 'HR & Talent Acquisition Executive',
                'icon' => 'ti-users',
                'badge' => 'Human Resources',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for managing end-to-end recruitment, seamless employee onboarding, biometric attendance/leave tracking, statutory payroll compliance, employee engagement, and positive workplace culture.\n\nKey Responsibilities:\n1. Source, screen, coordinate interviews, and close talent requirements across technical and business departments.\n2. Conduct candidate onboarding, KYC document verification, asset issuance, policy briefing, and orientation.\n3. Track daily biometric attendance, shift exceptions, leave requests, and compile monthly payroll inputs.\n4. Organize employee engagement celebrations, team building events, and resolve employee grievances proactively.\n5. Maintain up-to-date employee dossiers, compliance registers, HR software data, and assist in appraisal reviews.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Recruitment & Talent Acquisition Velocity',
                        'weightage' => 35,
                        'kpis' => [
                            ['metric' => 'Average Time-to-Hire for open position requirements', 'target' => '<= 21 days', 'frequency' => 'Monthly'],
                            ['metric' => 'Offer-to-joining conversion ratio', 'target' => '>= 85%', 'frequency' => 'Monthly'],
                            ['metric' => '90-day new hire retention rate', 'target' => '>= 90%', 'frequency' => 'Quarterly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Onboarding, Attendance & Payroll Compliance',
                        'weightage' => 30,
                        'kpis' => [
                            ['metric' => 'Onboarding KYC & document verification turnaround', 'target' => '<= 48 hours', 'frequency' => 'Monthly'],
                            ['metric' => 'Monthly payroll input calculation accuracy', 'target' => '100% error-free', 'frequency' => 'Monthly'],
                            ['metric' => 'Statutory & company policy compliance score', 'target' => '100% compliant', 'frequency' => 'Continuous'],
                        ]
                    ],
                    [
                        'kra_title' => 'Self Attendance, Punctuality & HR Role Modeling',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Self Monthly Attendance percentage', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality adherence & grace tolerance', 'target' => '<= 1 late mark/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Employee Engagement & Grievance Resolution',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Employee grievance resolution turnaround SLA', 'target' => '<= 48 hours', 'frequency' => 'Monthly'],
                            ['metric' => 'Monthly employee engagement & culture activities', 'target' => '>= 1 event/month', 'frequency' => 'Monthly'],
                        ]
                    ],
                ]
            ],
            'admin_ops' => [
                'key' => 'admin_ops',
                'industry' => 'hr_admin',
                'industry_label' => 'HR, Admin & Operations',
                'industry_icon' => 'ti-users',
                'title' => 'Administrative & Facility Operations Officer',
                'icon' => 'ti-building',
                'badge' => 'Admin & Facility',
                'attendance_target_percentage' => 97,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for office facility management, housekeeping supervision, vendor contract management, stationery/pantry supplies procurement, travel booking, asset tracking, and security monitoring.\n\nKey Responsibilities:\n1. Maintain clean, safe, and fully operational office premises, utilities, AC, lighting, and plumbing.\n2. Supervise housekeeping staff, security guards, and visitor reception protocols.\n3. Procure office stationery, consumables, pantry supplies, and manage monthly vendor bills.\n4. Manage corporate travel bookings, hotel reservations, cab logistics, and event arrangements.\n5. Maintain company fixed asset tags, physical condition registers, and annual maintenance contracts (AMC).",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Facility Uptime, Hygiene & Office Maintenance',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'Office facility hygiene and cleanliness audit score', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Facility complaint turnaround time (AC/Electrical/Plumbing)', 'target' => '<= 4 hours', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Vendor Management & Admin Cost Optimization',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Pantry & stationery procurement within approved budget', 'target' => '100% within budget', 'frequency' => 'Monthly'],
                            ['metric' => 'AMC renewal and preventive servicing compliance', 'target' => '100% on schedule', 'frequency' => 'Quarterly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Office Opening Punctuality & Discipline',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 97%', 'frequency' => 'Monthly'],
                            ['metric' => 'Office opening & closing protocol punctuality', 'target' => '100% punctual', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Asset Register & Visitor Protocol Compliance',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Fixed asset verification & physical audit accuracy', 'target' => '100% verified', 'frequency' => 'Quarterly'],
                            ['metric' => 'Visitor logbook & security CCTV monitoring compliance', 'target' => '100% compliant', 'frequency' => 'Continuous'],
                        ]
                    ],
                ]
            ],

            // ======================== 5. SALES, MARKETING & CRM ========================
            'sales_bd' => [
                'key' => 'sales_bd',
                'industry' => 'sales_marketing',
                'industry_label' => 'Sales, Retail & Marketing',
                'industry_icon' => 'ti-chart-arrows',
                'title' => 'Business Development & Corporate Sales Executive',
                'icon' => 'ti-trending-up',
                'badge' => 'B2B Sales & BD',
                'attendance_target_percentage' => 95,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for generating qualified B2B leads, conducting high-impact product presentations and software demonstrations, negotiating commercial contracts, and exceeding revenue targets.\n\nKey Responsibilities:\n1. Identify and prospect qualified corporate leads via cold outreach, LinkedIn Sales Navigator, and inbound funnels.\n2. Conduct engaging software demos tailored to prospective client business requirements and workflows.\n3. Prepare commercial proposals, handle price negotiations, and close new client subscription contracts.\n4. Maintain CRM pipeline discipline with real-time deal stage tracking, activity logging, and revenue forecasting.\n5. Coordinate with onboarding and support teams for frictionless client handover and high customer satisfaction.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Sales Target Achievement & Revenue Generation',
                        'weightage' => 45,
                        'kpis' => [
                            ['metric' => 'Monthly sales quota achievement vs target', 'target' => '>= 100%', 'frequency' => 'Monthly'],
                            ['metric' => 'Qualified demo-to-deal closure conversion rate', 'target' => '>= 25%', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Lead Generation & CRM Pipeline Discipline',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'New qualified corporate leads generated per month', 'target' => '>= 30 leads/mo', 'frequency' => 'Monthly'],
                            ['metric' => 'Daily CRM logging & timely prospect follow-ups', 'target' => '100% logged daily', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Client Demos & Presentation Excellence',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Live product demonstrations conducted per month', 'target' => '>= 15 demos/mo', 'frequency' => 'Monthly'],
                            ['metric' => 'Prospective client demo feedback & satisfaction rating', 'target' => '>= 9.0 / 10', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Punctuality & Sales Reviews',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance and client call punctuality', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Weekly sales pipeline review & forecast accuracy', 'target' => '100% prepared', 'frequency' => 'Weekly'],
                        ]
                    ],
                ]
            ],
            'retail_sales' => [
                'key' => 'retail_sales',
                'industry' => 'sales_marketing',
                'industry_label' => 'Sales, Retail & Marketing',
                'industry_icon' => 'ti-chart-arrows',
                'title' => 'Retail Showroom & Counter Sales Executive',
                'icon' => 'ti-building-store',
                'badge' => 'Retail Sales',
                'attendance_target_percentage' => 96,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 9.00,
                'job_description' => "Role Overview: Responsible for welcoming showroom walk-in customers, understanding customer preferences, explaining product features/pricing, upselling accessories, closing sales, and ensuring visual merchandising excellence.\n\nKey Responsibilities:\n1. Greet showroom visitors warmly, demonstrate products, and answer customer queries enthusiastically.\n2. Drive retail revenue, achieve daily/monthly sales targets, and upsell warranties/accessories.\n3. Maintain impeccable showroom display, product dusting, shelf tagging, and price label updates.\n4. Process POS counter billing, payment collection (Card/UPI/Cash), and invoice handover.\n5. Follow up with potential walk-in leads via phone calls/WhatsApp to achieve repeat footfalls.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Showroom Counter Sales Quota & Conversion',
                        'weightage' => 45,
                        'kpis' => [
                            ['metric' => 'Monthly showroom sales revenue vs assigned target', 'target' => '>= 100%', 'frequency' => 'Monthly'],
                            ['metric' => 'Walk-in customer footfall to billing conversion rate', 'target' => '>= 35%', 'frequency' => 'Monthly'],
                            ['metric' => 'Average transaction value (ATV) / Upselling score', 'target' => 'Target Achieved', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Customer Experience & Visual Merchandising',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Customer service feedback & Google review rating', 'target' => '>= 4.8 / 5.0', 'frequency' => 'Monthly'],
                            ['metric' => 'Showroom visual merchandising & product shelf audit', 'target' => '>= 95%', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Shift Attendance, Floor Presence & Punctuality',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 96%', 'frequency' => 'Monthly'],
                            ['metric' => 'Showroom floor shift punctuality & opening discipline', 'target' => '<= 2 late marks/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Counter Cash Reconciliation & Stock Security',
                        'weightage' => 10,
                        'kpis' => [
                            ['metric' => 'Daily POS counter cash/UPI tally reconciliation', 'target' => '100% matched', 'frequency' => 'Daily'],
                            ['metric' => 'Zero shrinkage / display product damage', 'target' => '0 discrepancies', 'frequency' => 'Monthly'],
                        ]
                    ],
                ]
            ],
            'digital_marketing' => [
                'key' => 'digital_marketing',
                'industry' => 'sales_marketing',
                'industry_label' => 'Sales, Retail & Marketing',
                'industry_icon' => 'ti-chart-arrows',
                'title' => 'Digital Marketing & SEO Specialist',
                'icon' => 'ti-brand-google',
                'badge' => 'Marketing & SEO',
                'attendance_target_percentage' => 95,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for planning and executing digital marketing campaigns, Search Engine Optimization (SEO), PPC advertising, social media growth, and content marketing to drive inbound leads and brand awareness.\n\nKey Responsibilities:\n1. Execute organic SEO strategies (technical audits, on-page optimization, content creation, and quality link building).\n2. Manage paid advertising campaigns across Google Ads, Meta Ads, and LinkedIn Ads with optimized ROAS and low CPL.\n3. Create and schedule engaging social media content calendars, banners, case studies, and email drip sequences.\n4. Monitor website performance using Google Analytics 4, Search Console, and compile monthly ROI reports.\n5. Optimize landing page conversion rates (CRO) and test marketing funnels for maximum customer acquisition.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Organic Search (SEO) & Traffic Growth',
                        'weightage' => 35,
                        'kpis' => [
                            ['metric' => 'Monthly organic website traffic growth rate', 'target' => '>= 15% MoM', 'frequency' => 'Monthly'],
                            ['metric' => 'Primary keywords ranked in Top 10 Google search results', 'target' => '>= 25 keywords', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Paid Ads Management & Lead Generation (ROAS)',
                        'weightage' => 30,
                        'kpis' => [
                            ['metric' => 'Cost Per Lead (CPL) within approved budget target', 'target' => 'Target Achieved', 'frequency' => 'Monthly'],
                            ['metric' => 'Paid advertising campaign Return on Ad Spend (ROAS)', 'target' => '>= 3.5x ROAS', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Social Media Engagement & Content Publishing',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Social media followers, impressions & engagement growth', 'target' => '>= 20% MoM', 'frequency' => 'Monthly'],
                            ['metric' => 'Content publishing consistency as per editorial calendar', 'target' => '100% on schedule', 'frequency' => 'Weekly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Punctuality & Analytics Reporting',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Monthly executive marketing analytics & ROI report', 'target' => '1st of every month', 'frequency' => 'Monthly'],
                        ]
                    ],
                ]
            ],
            'telecaller_support' => [
                'key' => 'telecaller_support',
                'industry' => 'sales_marketing',
                'industry_label' => 'Sales, Retail & Marketing',
                'industry_icon' => 'ti-chart-arrows',
                'title' => 'Telecalling & Customer Care Executive (CRM/BPO)',
                'icon' => 'ti-headset',
                'badge' => 'Support & Telecalling',
                'attendance_target_percentage' => 97,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for making outbound sales/follow-up phone calls, answering inbound customer support queries, solving client complaints, booking appointments for sales executives, and updating CRM records in real time.\n\nKey Responsibilities:\n1. Make targeted outbound calls daily to prospective client databases, qualify interest, and book demo appointments.\n2. Handle inbound customer inquiries and support tickets with professionalism, empathy, and speed.\n3. Maintain accurate call status, conversation remarks, and callback reminders in the CRM system.\n4. Escalate critical technical complaints to senior teams and follow up until complete resolution.\n5. Collect customer feedback, NPS survey ratings, and assist in client retention campaigns.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Daily Call Volume, Talktime & Appointment Booking',
                        'weightage' => 45,
                        'kpis' => [
                            ['metric' => 'Daily connected calls / connected talktime quota', 'target' => '>= 80 calls/day', 'frequency' => 'Daily'],
                            ['metric' => 'Qualified demo appointments booked per week', 'target' => '>= 15 bookings/wk', 'frequency' => 'Weekly'],
                        ]
                    ],
                    [
                        'kra_title' => 'First Contact Resolution (FCR) & CSAT Score',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Customer satisfaction (CSAT) rating on call surveys', 'target' => '>= 92%', 'frequency' => 'Monthly'],
                            ['metric' => 'First Contact Resolution (FCR) on support inquiries', 'target' => '>= 80%', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Shift Attendance, Login Punctuality & Adherence',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 97%', 'frequency' => 'Monthly'],
                            ['metric' => 'Dialer login punctuality & schedule adherence', 'target' => '<= 1 late mark/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'CRM Data Hygiene & Follow-Up SLA',
                        'weightage' => 10,
                        'kpis' => [
                            ['metric' => '100% call outcome logging in CRM after every call', 'target' => '100% logged', 'frequency' => 'Daily'],
                            ['metric' => 'Callback commitments attended within scheduled window', 'target' => '>= 98%', 'frequency' => 'Daily'],
                        ]
                    ],
                ]
            ],

            // ======================== 6. LOGISTICS, DISPATCH & PROCUREMENT ========================
            'logistics_dispatch' => [
                'key' => 'logistics_dispatch',
                'industry' => 'logistics',
                'industry_label' => 'Logistics & Supply Chain',
                'industry_icon' => 'ti-truck-delivery',
                'title' => 'Logistics & Dispatch Coordinator',
                'icon' => 'ti-truck-delivery',
                'badge' => 'Logistics & Dispatch',
                'attendance_target_percentage' => 97,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 9.00,
                'job_description' => "Role Overview: Responsible for coordinating outbound product dispatches, freight transporter negotiations, vehicle placement, packing list verification, LR (Lorry Receipt) tracking, and on-time client delivery.\n\nKey Responsibilities:\n1. Plan daily dispatch shipments as per finished goods readiness and client delivery deadlines.\n2. Negotiate competitive freight charges with logistics providers, courier partners, and local transporters.\n3. Verify cargo packaging integrity, box counts, gross weight, and dispatch documentation.\n4. Track live consignment movement, resolve transit delays, and update tracking numbers in ERP.\n5. Obtain signed Proof of Delivery (POD) / receiver challans and reconcile freight invoices.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'On-Time In-Full (OTIF) Dispatch Delivery',
                        'weightage' => 45,
                        'kpis' => [
                            ['metric' => 'On-Time In-Full (OTIF) customer dispatch rate', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Same-day dispatch clearance for ready finished goods', 'target' => '>= 95%', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Freight Cost Optimization & POD Management',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Freight cost per kilogram / ton within budget', 'target' => 'Target Achieved', 'frequency' => 'Monthly'],
                            ['metric' => 'Signed Proof of Delivery (POD) collection turnaround', 'target' => '<= 48 hours', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Shift Punctuality & Loading Supervision',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 97%', 'frequency' => 'Monthly'],
                            ['metric' => 'Vehicle loading punctuality & dispatch gate clearance', 'target' => '<= 2 late marks/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Transit Cargo Safety & Damage Prevention',
                        'weightage' => 10,
                        'kpis' => [
                            ['metric' => 'Transit damage / shortage claim incidents', 'target' => '< 0.2% of goods', 'frequency' => 'Monthly'],
                            ['metric' => 'Transporter SLA compliance and performance score', 'target' => '>= 90%', 'frequency' => 'Quarterly'],
                        ]
                    ],
                ]
            ],
            'purchase_officer' => [
                'key' => 'purchase_officer',
                'industry' => 'logistics',
                'industry_label' => 'Logistics & Supply Chain',
                'industry_icon' => 'ti-truck-delivery',
                'title' => 'Purchase & Procurement Officer',
                'icon' => 'ti-shopping-cart',
                'badge' => 'Purchase & Procurement',
                'attendance_target_percentage' => 98,
                'late_mark_tolerance' => 1,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for sourcing raw materials, machinery spare parts, tools, and office supplies at the best commercial prices, evaluating vendor quotations, issuing Purchase Orders (PO), and tracking vendor delivery schedules.\n\nKey Responsibilities:\n1. Collect and compare minimum 3 vendor quotations for all purchase requisitions.\n2. Negotiate optimal purchase pricing, payment terms (credit days), and delivery timelines.\n3. Issue formal Purchase Orders (PO) with clear technical specifications, inspection clauses, and terms.\n4. Follow up with suppliers to ensure zero production line stoppage due to raw material stockouts.\n5. Conduct annual vendor performance appraisals based on quality, pricing, and on-time delivery.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Procurement Cost Savings & Negotiation',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'Procurement cost savings achieved vs benchmark budget', 'target' => '>= 5% savings', 'frequency' => 'Monthly'],
                            ['metric' => 'Purchase Order (PO) turnaround time upon requisition', 'target' => '<= 24 hours', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Zero Stockout & Supplier On-Time Delivery (OTD)',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Supplier On-Time Delivery (OTD) rate', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Zero production downtime due to raw material stockouts', 'target' => '0 stockouts', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Punctuality & Commercial Review Meetings',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality adherence & grace tolerance', 'target' => '<= 1 late mark/mo', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Vendor Quality SLA & Payment Reconciliation',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Vendor rejection rate & credit note settlement SLA', 'target' => '<= 3 days', 'frequency' => 'Monthly'],
                            ['metric' => 'Vendor rating scorecard & documentation accuracy', 'target' => '100% updated', 'frequency' => 'Quarterly'],
                        ]
                    ],
                ]
            ],

            // ======================== 7. GENERAL OPERATIONS ========================
            'general_operations' => [
                'key' => 'general_operations',
                'industry' => 'general',
                'industry_label' => 'General Operations',
                'industry_icon' => 'ti-briefcase',
                'title' => 'Operations & General Executive',
                'icon' => 'ti-briefcase',
                'badge' => 'General / Operations',
                'attendance_target_percentage' => 95,
                'late_mark_tolerance' => 2,
                'daily_working_hours_target' => 8.50,
                'job_description' => "Role Overview: Responsible for managing day-to-day administrative and operational functions, coordinating inter-departmental workflows, and ensuring adherence to company service standards.\n\nKey Responsibilities:\n1. Execute assigned daily tasks, deliver projects with high precision, quality, and punctuality.\n2. Coordinate with team members, facilitate cross-functional communication, and report progress daily.\n3. Adhere strictly to internal standard operating procedures (SOPs), data confidentiality, and safety norms.\n4. Maintain operational registers, documentation, inventory logs, and system data entry.\n5. Support continuous process improvement initiatives and participate enthusiastically in organizational growth.",
                'kra_kpis' => [
                    [
                        'kra_title' => 'Core Operational Task Execution & Delivery',
                        'weightage' => 40,
                        'kpis' => [
                            ['metric' => 'On-time daily task completion rate', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Accuracy and quality of operational deliverables', 'target' => '>= 98%', 'frequency' => 'Monthly'],
                        ]
                    ],
                    [
                        'kra_title' => 'Attendance, Punctuality & Shift Discipline',
                        'weightage' => 25,
                        'kpis' => [
                            ['metric' => 'Monthly Attendance Percentage', 'target' => '>= 95%', 'frequency' => 'Monthly'],
                            ['metric' => 'Punctuality & Grace Late Marks adherence', 'target' => '<= 2 late marks/mo', 'frequency' => 'Monthly'],
                            ['metric' => 'Daily working hours fulfillment', 'target' => '8.50 Hrs/Day', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'SOP Compliance & Data Accuracy',
                        'weightage' => 20,
                        'kpis' => [
                            ['metric' => 'Adherence to internal SOPs and company policies', 'target' => '100% compliant', 'frequency' => 'Continuous'],
                            ['metric' => 'Daily status reporting and work logging in ERP', 'target' => '100% compliant', 'frequency' => 'Daily'],
                        ]
                    ],
                    [
                        'kra_title' => 'Team Agility & Continuous Learning',
                        'weightage' => 15,
                        'kpis' => [
                            ['metric' => 'Participation in team meetings & collaborative initiatives', 'target' => 'Active', 'frequency' => 'Weekly'],
                            ['metric' => 'Completion of mandatory training and skill modules', 'target' => '100% complete', 'frequency' => 'Quarterly'],
                        ]
                    ],
                ]
            ],
        ];
    }

    /**
     * Get industry categories grouped
     */
    public static function getIndustryCategories(): array
    {
        return [
            'all' => ['label' => 'All Categories', 'icon' => 'ti-apps'],
            'white_collar' => ['label' => 'White Collar (CEO / HR / Exec)', 'icon' => 'ti-crown'],
            'grey_collar' => ['label' => 'Grey Collar (Supervisors / Tech)', 'icon' => 'ti-user-check'],
            'blue_collar' => ['label' => 'Blue Collar (Workers / Plant)', 'icon' => 'ti-tools'],
            'industrial' => ['label' => 'Industrial & Manufacturing', 'icon' => 'ti-building-factory-2'],
            'it_tech' => ['label' => 'IT & Software', 'icon' => 'ti-device-laptop'],
            'accounts' => ['label' => 'Accounts & Finance', 'icon' => 'ti-receipt-tax'],
            'hr_admin' => ['label' => 'HR, Admin & Operations', 'icon' => 'ti-users'],
            'sales_marketing' => ['label' => 'Sales, Retail & Marketing', 'icon' => 'ti-chart-arrows'],
            'logistics' => ['label' => 'Logistics & Supply Chain', 'icon' => 'ti-truck-delivery'],
            'general' => ['label' => 'General Operations', 'icon' => 'ti-briefcase'],
        ];
    }

    /**
     * Get role template matched by designation name or designation id
     */
    public static function getTemplateForDesignation($designationNameOrId): array
    {
        $templates = self::getRoleTemplates();

        if (is_numeric($designationNameOrId)) {
            $designation = Designation::find($designationNameOrId);
            $name = $designation?->name ?? '';
        } else {
            $name = (string)$designationNameOrId;
        }

        $lower = strtolower($name);

        // White Collar (CEO / Executive & Main HR)
        if (str_contains($lower, 'ceo') || str_contains($lower, 'managing director') || str_contains($lower, 'director') || str_contains($lower, 'founder') || str_contains($lower, 'plant head') || str_contains($lower, 'chief executive')) {
            return $templates['ceo_director'];
        }

        if (str_contains($lower, 'main hr') || str_contains($lower, 'hr head') || str_contains($lower, 'senior hr') || str_contains($lower, 'admin head') || str_contains($lower, 'hr manager') || str_contains($lower, 'hr & admin')) {
            return $templates['main_hr'];
        }

        // Grey Collar (Supervisors & Line Incharge)
        if (str_contains($lower, 'supervisor') || str_contains($lower, 'shift incharge') || str_contains($lower, 'line leader') || str_contains($lower, 'shopfloor supervisor') || str_contains($lower, 'production supervisor')) {
            return $templates['plant_supervisor'];
        }

        // Blue Collar (Plant Worker / Labor / Helper)
        if (str_contains($lower, 'worker') || str_contains($lower, 'helper') || str_contains($lower, 'labor') || str_contains($lower, 'labour') || str_contains($lower, 'packer') || str_contains($lower, 'assembler') || str_contains($lower, 'plant worker')) {
            return $templates['plant_worker'];
        }

        // IT & Software
        if (str_contains($lower, 'develop') || str_contains($lower, 'engineer') && !str_contains($lower, 'plant') && !str_contains($lower, 'maintenance') || str_contains($lower, 'programmer') || str_contains($lower, 'coder') || str_contains($lower, 'php') || str_contains($lower, 'laravel') || str_contains($lower, 'react') || str_contains($lower, 'flutter') || str_contains($lower, 'node') || str_contains($lower, 'python') || str_contains($lower, 'full stack') || str_contains($lower, 'frontend') || str_contains($lower, 'backend')) {
            return $templates['developer'];
        }

        if (str_contains($lower, 'design') || str_contains($lower, 'ui') || str_contains($lower, 'ux') || str_contains($lower, 'figma') || str_contains($lower, 'graphic') || str_contains($lower, 'creative') || str_contains($lower, 'visual')) {
            return $templates['ui_ux'];
        }

        if (str_contains($lower, 'qa') || str_contains($lower, 'test') || str_contains($lower, 'automation') || str_contains($lower, 'sqa') || str_contains($lower, 'software tester')) {
            return $templates['qa_tester'];
        }

        if (str_contains($lower, 'devops') || str_contains($lower, 'sysadmin') || str_contains($lower, 'system admin') || str_contains($lower, 'it support') || str_contains($lower, 'hardware') || str_contains($lower, 'network admin') || str_contains($lower, 'cloud')) {
            return $templates['devops_it'];
        }

        if (str_contains($lower, 'project manager') || str_contains($lower, 'scrum') || str_contains($lower, 'tech lead') || str_contains($lower, 'delivery manager') || str_contains($lower, 'product manager')) {
            return $templates['project_manager'];
        }

        // Industrial & Manufacturing
        if (str_contains($lower, 'production') || str_contains($lower, 'plant') || str_contains($lower, 'factory') || str_contains($lower, 'shopfloor') || str_contains($lower, 'works manager')) {
            return $templates['production_manager'];
        }

        if (str_contains($lower, 'cnc') || str_contains($lower, 'vmc') || str_contains($lower, 'lathe') || str_contains($lower, 'operator') || str_contains($lower, 'machinist') || str_contains($lower, 'fitter') || str_contains($lower, 'turner') || str_contains($lower, 'welder') || str_contains($lower, 'technician')) {
            return $templates['cnc_operator'];
        }

        if (str_contains($lower, 'qc') || str_contains($lower, 'quality control') || str_contains($lower, 'quality inspector') || str_contains($lower, 'safety officer') || str_contains($lower, 'pdi') || str_contains($lower, 'inspector')) {
            return $templates['qc_inspector'];
        }

        if (str_contains($lower, 'maintenance') || str_contains($lower, 'electrician') || str_contains($lower, 'electrical') || str_contains($lower, 'utility') || str_contains($lower, 'mechanic')) {
            return $templates['maintenance_eng'];
        }

        if (str_contains($lower, 'store') || str_contains($lower, 'warehouse') || str_contains($lower, 'inventory') || str_contains($lower, 'material incharge') || str_contains($lower, 'stock incharge')) {
            return $templates['store_inventory'];
        }

        // Accounts & Finance
        if (str_contains($lower, 'account') || str_contains($lower, 'finance') || str_contains($lower, 'gst') || str_contains($lower, 'tax') || str_contains($lower, 'tally') || str_contains($lower, 'audit') || str_contains($lower, 'ca') || str_contains($lower, 'cfo')) {
            return $templates['accountant'];
        }

        if (str_contains($lower, 'billing') || str_contains($lower, 'invoice') || str_contains($lower, 'e-way') || str_contains($lower, 'challan')) {
            return $templates['billing_executive'];
        }

        // HR & Admin
        if (str_contains($lower, 'hr') || str_contains($lower, 'human resource') || str_contains($lower, 'recruit') || str_contains($lower, 'talent') || str_contains($lower, 'people') || str_contains($lower, 'payroll')) {
            return $templates['hr_executive'];
        }

        if (str_contains($lower, 'admin') || str_contains($lower, 'facility') || str_contains($lower, 'front desk') || str_contains($lower, 'reception') || str_contains($lower, 'office coordinator')) {
            return $templates['admin_ops'];
        }

        // Sales & Marketing
        if (str_contains($lower, 'sales') || str_contains($lower, 'business dev') || str_contains($lower, 'bd') || str_contains($lower, 'bde') || str_contains($lower, 'bdm') || str_contains($lower, 'client relationship') || str_contains($lower, 'commercial')) {
            return $templates['sales_bd'];
        }

        if (str_contains($lower, 'retail') || str_contains($lower, 'showroom') || str_contains($lower, 'counter sales') || str_contains($lower, 'store sales') || str_contains($lower, 'floor executive')) {
            return $templates['retail_sales'];
        }

        if (str_contains($lower, 'market') || str_contains($lower, 'seo') || str_contains($lower, 'digital') || str_contains($lower, 'social media') || str_contains($lower, 'content') || str_contains($lower, 'ppc') || str_contains($lower, 'growth')) {
            return $templates['digital_marketing'];
        }

        if (str_contains($lower, 'telecall') || str_contains($lower, 'call center') || str_contains($lower, 'bpo') || str_contains($lower, 'customer care') || str_contains($lower, 'support executive') || str_contains($lower, 'helpdesk')) {
            return $templates['telecaller_support'];
        }

        // Logistics & Purchase
        if (str_contains($lower, 'logistics') || str_contains($lower, 'dispatch') || str_contains($lower, 'transport') || str_contains($lower, 'supply chain') || str_contains($lower, 'shipping') || str_contains($lower, 'delivery coordinator')) {
            return $templates['logistics_dispatch'];
        }

        if (str_contains($lower, 'purchase') || str_contains($lower, 'procurement') || str_contains($lower, 'buyer') || str_contains($lower, 'sourcing')) {
            return $templates['purchase_officer'];
        }

        return $templates['general_operations'];
    }

    /**
     * Get structured KRA & KPI items (default or configured)
     */
    public function getParsedKraKpiAttribute()
    {
        if (!empty($this->kra_kpi_details) && is_array($this->kra_kpi_details)) {
            return $this->kra_kpi_details;
        }

        // Match automatically from designation
        $matchedTemplate = self::getTemplateForDesignation($this->designation?->name ?? $this->designation_id);
        return $matchedTemplate['kra_kpis'];
    }

    /**
     * Get default job description based on role
     */
    public function getDefaultJobDescription(): string
    {
        if (!empty($this->job_description)) {
            return $this->job_description;
        }

        $matchedTemplate = self::getTemplateForDesignation($this->designation?->name ?? $this->designation_id);
        return $matchedTemplate['job_description'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function reportingManager()
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    public function buddy()
    {
        return $this->belongsTo(Employee::class, 'buddy_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function subDepartment()
    {
        return $this->belongsTo(SubDepartment::class, 'sub_department_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function role()
    {
        return $this->belongsTo(TeamRole::class, 'role_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function documents()
    {
        return $this->hasMany(OnboardingDocument::class, 'onboarding_id');
    }

    public function trainings()
    {
        return $this->hasMany(OnboardingTraining::class, 'onboarding_id')->orderBy('module_number', 'asc');
    }

    public function assets()
    {
        return $this->hasMany(OnboardingAsset::class, 'onboarding_id');
    }

    /**
     * Recalculate progress percentage based on steps & completed items
     */
    public function calculateProgress(): int
    {
        $totalWeight = 0;
        
        // 1. Basic Info (Step 1)
        if (!empty($this->full_name) && !empty($this->email) && !empty($this->contact_number)) {
            $totalWeight += 20;
        }

        // 2. Documents (Step 2)
        $docsCount = $this->documents()->count();
        $verifiedDocs = $this->documents()->where('status', 'verified')->count();
        if ($docsCount > 0) {
            $totalWeight += round(($verifiedDocs / max($docsCount, 1)) * 20);
        }

        // 3. Company Overview (Step 3)
        if ($this->company_overview_acknowledged) {
            $totalWeight += 15;
        }

        // 4. Trainings (Step 4)
        $trainingsCount = $this->trainings()->count();
        $completedTrainings = $this->trainings()->where('status', 'completed')->count();
        if ($trainingsCount > 0) {
            $totalWeight += round(($completedTrainings / max($trainingsCount, 1)) * 20);
        } else {
            $totalWeight += 5;
        }

        // 5. Assets (Step 5)
        $assetsCount = $this->assets()->count();
        $assignedAssets = $this->assets()->whereIn('status', ['assigned', 'handed_over'])->count();
        if ($assetsCount > 0) {
            $totalWeight += round(($assignedAssets / max($assetsCount, 1)) * 15);
        }

        // 6. Reporting manager & finalization (Step 6)
        if ($this->reporting_manager_id) {
            $totalWeight += 10;
        }

        $percentage = min(100, max(0, $totalWeight));
        $this->progress_percentage = $percentage;
        if ($percentage >= 100) {
            $this->status = 'completed';
        }
        $this->saveQuietly();

        return $percentage;
    }
}
