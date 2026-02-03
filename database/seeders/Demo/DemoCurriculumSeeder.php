<?php

namespace Database\Seeders\Demo;

use App\Models\Subject;
use App\Models\CurriculumChapter;
use App\Models\CurriculumTopic;
use Illuminate\Support\Str;

class DemoCurriculumSeeder extends DemoBaseSeeder
{
    public function run(array $subjects): void
    {
        $this->command->info('Creating Curriculum Chapters and Topics...');

        $totalChapters = 0;
        $totalTopics = 0;

        foreach ($subjects as $gradeLevel => $gradeSubjects) {
            foreach ($gradeSubjects as $subjectData) {
                $subject = $subjectData['subject'];
                $chapters = $this->getChaptersForSubject($subject->name, $gradeLevel);

                $chapterOrder = 1;
                foreach ($chapters as $chapterTitle => $topics) {
                    $chapter = CurriculumChapter::create([
                        'id' => Str::uuid(),
                        'subject_id' => $subject->id,
                        'title' => $chapterTitle,
                        'description' => 'Comprehensive study of ' . $chapterTitle,
                        'order' => $chapterOrder++,
                    ]);

                    $totalChapters++;

                    $topicOrder = 1;
                    foreach ($topics as $topicTitle) {
                        CurriculumTopic::create([
                            'id' => Str::uuid(),
                            'chapter_id' => $chapter->id,
                            'title' => $topicTitle,
                            'description' => 'Detailed understanding of ' . $topicTitle,
                            'order' => $topicOrder++,
                        ]);

                        $totalTopics++;
                    }
                }
            }
        }

        $this->command->info("  Created {$totalChapters} chapters with {$totalTopics} topics total.");
    }

    private function getChaptersForSubject(string $subjectName, int $gradeLevel): array
    {
        return match ($subjectName) {
            'Mathematics' => $this->getMathematicsChapters($gradeLevel),
            'English' => $this->getEnglishChapters($gradeLevel),
            'Science', 'General Science' => $this->getScienceChapters($gradeLevel),
            'Physics' => $this->getPhysicsChapters($gradeLevel),
            'Chemistry' => $this->getChemistryChapters($gradeLevel),
            'Biology' => $this->getBiologyChapters($gradeLevel),
            'Myanmar' => $this->getMyanmarChapters($gradeLevel),
            'History' => $this->getHistoryChapters($gradeLevel),
            'Geography' => $this->getGeographyChapters($gradeLevel),
            'Social Studies' => $this->getSocialStudiesChapters($gradeLevel),
            'Art', 'Art & Craft' => $this->getArtChapters($gradeLevel),
            'Physical Education' => $this->getPhysicalEducationChapters($gradeLevel),
            default => $this->getGenericChapters($gradeLevel),
        };
    }

    private function getMathematicsChapters(int $gradeLevel): array
    {
        if ($gradeLevel <= 4) {
            return [
                'Chapter 1: Numbers and Counting' => [
                    'Counting Objects',
                    'Number Recognition',
                    'Place Value',
                    'Comparing Numbers',
                    'Ordering Numbers',
                    'Skip Counting',
                    'Even and Odd Numbers',
                ],
                'Chapter 2: Addition and Subtraction' => [
                    'Basic Addition',
                    'Addition with Regrouping',
                    'Basic Subtraction',
                    'Subtraction with Borrowing',
                    'Word Problems',
                    'Mental Math Strategies',
                ],
                'Chapter 3: Multiplication and Division' => [
                    'Introduction to Multiplication',
                    'Multiplication Tables',
                    'Introduction to Division',
                    'Division Facts',
                    'Relationship between Operations',
                ],
                'Chapter 4: Shapes and Patterns' => [
                    'Basic 2D Shapes',
                    'Basic 3D Shapes',
                    'Pattern Recognition',
                    'Creating Patterns',
                    'Symmetry',
                ],
                'Chapter 5: Measurement' => [
                    'Length and Height',
                    'Weight and Mass',
                    'Capacity and Volume',
                    'Time',
                    'Money',
                ],
            ];
        } elseif ($gradeLevel <= 8) {
            return [
                'Chapter 1: Number Systems' => [
                    'Integers and Number Line',
                    'Rational Numbers',
                    'Fractions and Decimals',
                    'Percentages',
                    'Number Properties',
                    'Prime and Composite Numbers',
                    'LCM and HCF',
                ],
                'Chapter 2: Algebraic Expressions' => [
                    'Variables and Constants',
                    'Algebraic Terms',
                    'Operations on Expressions',
                    'Linear Equations',
                    'Word Problems',
                ],
                'Chapter 3: Geometry' => [
                    'Lines and Angles',
                    'Triangles',
                    'Quadrilaterals',
                    'Circles',
                    'Perimeter and Area',
                ],
                'Chapter 4: Data Handling' => [
                    'Collection of Data',
                    'Organization of Data',
                    'Bar Graphs',
                    'Pie Charts',
                    'Mean, Median, Mode',
                ],
                'Chapter 5: Ratio and Proportion' => [
                    'Understanding Ratios',
                    'Equivalent Ratios',
                    'Proportions',
                    'Direct Proportion',
                    'Inverse Proportion',
                ],
                'Chapter 6: Mensuration' => [
                    'Area of Rectangles',
                    'Area of Triangles',
                    'Area of Circles',
                    'Surface Area',
                    'Volume',
                ],
            ];
        } else {
            return [
                'Chapter 1: Real Numbers' => [
                    'Rational and Irrational Numbers',
                    'Real Number System',
                    'Laws of Exponents',
                    'Surds and Rationalization',
                ],
                'Chapter 2: Polynomials' => [
                    'Introduction to Polynomials',
                    'Operations on Polynomials',
                    'Factorization',
                    'Remainder and Factor Theorem',
                ],
                'Chapter 3: Linear Equations' => [
                    'Linear Equations in Two Variables',
                    'Graphical Solution',
                    'Algebraic Methods',
                    'Applications',
                ],
                'Chapter 4: Quadratic Equations' => [
                    'Standard Form',
                    'Factorization Method',
                    'Completing the Square',
                    'Quadratic Formula',
                    'Nature of Roots',
                ],
                'Chapter 5: Coordinate Geometry' => [
                    'Distance Formula',
                    'Section Formula',
                    'Area of Triangle',
                    'Equation of a Line',
                ],
                'Chapter 6: Trigonometry' => [
                    'Trigonometric Ratios',
                    'Trigonometric Identities',
                    'Heights and Distances',
                    'Applications',
                ],
                'Chapter 7: Statistics' => [
                    'Measures of Central Tendency',
                    'Measures of Dispersion',
                    'Frequency Distribution',
                    'Graphical Representation',
                ],
                'Chapter 8: Probability' => [
                    'Experimental Probability',
                    'Theoretical Probability',
                    'Addition Rule',
                    'Multiplication Rule',
                ],
            ];
        }
    }

    private function getEnglishChapters(int $gradeLevel): array
    {
        if ($gradeLevel <= 4) {
            return [
                'Chapter 1: Alphabet and Phonics' => [
                    'Letter Recognition',
                    'Letter Sounds',
                    'Vowels and Consonants',
                    'Blending Sounds',
                ],
                'Chapter 2: Reading Skills' => [
                    'Sight Words',
                    'Simple Sentences',
                    'Reading Comprehension',
                    'Story Reading',
                ],
                'Chapter 3: Writing Skills' => [
                    'Letter Formation',
                    'Word Writing',
                    'Sentence Writing',
                    'Creative Writing',
                ],
                'Chapter 4: Grammar Basics' => [
                    'Nouns',
                    'Verbs',
                    'Adjectives',
                    'Simple Sentences',
                ],
                'Chapter 5: Vocabulary' => [
                    'Common Words',
                    'Action Words',
                    'Describing Words',
                    'Opposites',
                ],
            ];
        } elseif ($gradeLevel <= 8) {
            return [
                'Chapter 1: Reading Comprehension' => [
                    'Main Ideas',
                    'Supporting Details',
                    'Inference',
                    'Context Clues',
                ],
                'Chapter 2: Grammar' => [
                    'Parts of Speech',
                    'Tenses',
                    'Subject-Verb Agreement',
                    'Active and Passive Voice',
                ],
                'Chapter 3: Writing Skills' => [
                    'Paragraph Writing',
                    'Essay Writing',
                    'Letter Writing',
                    'Story Writing',
                ],
                'Chapter 4: Literature' => [
                    'Poetry Analysis',
                    'Short Stories',
                    'Drama',
                    'Literary Devices',
                ],
                'Chapter 5: Vocabulary Building' => [
                    'Word Roots',
                    'Prefixes and Suffixes',
                    'Synonyms and Antonyms',
                    'Idioms and Phrases',
                ],
            ];
        } else {
            return [
                'Chapter 1: Advanced Reading' => [
                    'Critical Reading',
                    'Analytical Skills',
                    'Text Analysis',
                    'Comparative Reading',
                ],
                'Chapter 2: Advanced Grammar' => [
                    'Complex Sentences',
                    'Clauses',
                    'Reported Speech',
                    'Conditionals',
                ],
                'Chapter 3: Composition' => [
                    'Argumentative Essays',
                    'Descriptive Writing',
                    'Narrative Writing',
                    'Formal Letters',
                ],
                'Chapter 4: Literature Studies' => [
                    'Novel Analysis',
                    'Poetry Interpretation',
                    'Drama Study',
                    'Literary Criticism',
                ],
                'Chapter 5: Communication Skills' => [
                    'Public Speaking',
                    'Debate',
                    'Presentation Skills',
                    'Interview Techniques',
                ],
            ];
        }
    }

    private function getScienceChapters(int $gradeLevel): array
    {
        if ($gradeLevel <= 4) {
            return [
                'Chapter 1: Living Things' => [
                    'Plants Around Us',
                    'Animals Around Us',
                    'Parts of Plants',
                    'Parts of Animals',
                ],
                'Chapter 2: Our Body' => [
                    'Body Parts',
                    'Sense Organs',
                    'Healthy Habits',
                    'Food and Nutrition',
                ],
                'Chapter 3: Our Environment' => [
                    'Air and Water',
                    'Weather',
                    'Day and Night',
                    'Seasons',
                ],
                'Chapter 4: Matter Around Us' => [
                    'Solids, Liquids, Gases',
                    'Properties of Materials',
                    'Changes in Matter',
                ],
                'Chapter 5: Simple Machines' => [
                    'Push and Pull',
                    'Simple Tools',
                    'Wheels and Levers',
                ],
            ];
        } else {
            return [
                'Chapter 1: Life Processes' => [
                    'Nutrition',
                    'Respiration',
                    'Transportation',
                    'Excretion',
                ],
                'Chapter 2: Matter and Materials' => [
                    'States of Matter',
                    'Physical and Chemical Changes',
                    'Mixtures and Solutions',
                    'Separation Techniques',
                ],
                'Chapter 3: Energy and Motion' => [
                    'Forms of Energy',
                    'Energy Transformation',
                    'Force and Motion',
                    'Simple Machines',
                ],
                'Chapter 4: Natural Resources' => [
                    'Air and Water',
                    'Soil',
                    'Forests',
                    'Conservation',
                ],
                'Chapter 5: Our Universe' => [
                    'Solar System',
                    'Earth and Moon',
                    'Stars and Constellations',
                ],
                'Chapter 6: Light and Sound' => [
                    'Properties of Light',
                    'Reflection and Refraction',
                    'Properties of Sound',
                    'Applications',
                ],
            ];
        }
    }

    private function getPhysicsChapters(int $gradeLevel): array
    {
        return [
            'Chapter 1: Motion and Force' => [
                'Types of Motion',
                'Speed and Velocity',
                'Acceleration',
                'Newton\'s Laws of Motion',
                'Friction',
            ],
            'Chapter 2: Work, Energy and Power' => [
                'Work Done',
                'Forms of Energy',
                'Kinetic and Potential Energy',
                'Conservation of Energy',
                'Power',
            ],
            'Chapter 3: Gravitation' => [
                'Universal Law of Gravitation',
                'Free Fall',
                'Mass and Weight',
                'Thrust and Pressure',
            ],
            'Chapter 4: Light' => [
                'Reflection of Light',
                'Spherical Mirrors',
                'Refraction of Light',
                'Lenses',
                'Human Eye',
            ],
            'Chapter 5: Electricity' => [
                'Electric Current',
                'Electric Potential',
                'Ohm\'s Law',
                'Series and Parallel Circuits',
                'Electric Power',
            ],
            'Chapter 6: Magnetism' => [
                'Magnetic Fields',
                'Electromagnetic Induction',
                'Electric Motor',
                'Electric Generator',
            ],
            'Chapter 7: Sound' => [
                'Production of Sound',
                'Propagation of Sound',
                'Characteristics of Sound',
                'Applications of Sound',
            ],
        ];
    }

    private function getChemistryChapters(int $gradeLevel): array
    {
        return [
            'Chapter 1: Matter and Its Composition' => [
                'Physical and Chemical Properties',
                'Elements, Compounds, Mixtures',
                'Atomic Structure',
                'Periodic Table',
            ],
            'Chapter 2: Chemical Reactions' => [
                'Types of Chemical Reactions',
                'Chemical Equations',
                'Balancing Equations',
                'Oxidation and Reduction',
            ],
            'Chapter 3: Acids, Bases and Salts' => [
                'Properties of Acids',
                'Properties of Bases',
                'pH Scale',
                'Neutralization',
                'Common Salts',
            ],
            'Chapter 4: Metals and Non-metals' => [
                'Properties of Metals',
                'Properties of Non-metals',
                'Reactivity Series',
                'Corrosion',
            ],
            'Chapter 5: Carbon Compounds' => [
                'Covalent Bonding',
                'Hydrocarbons',
                'Functional Groups',
                'Polymers',
            ],
            'Chapter 6: Chemical Bonding' => [
                'Ionic Bonding',
                'Covalent Bonding',
                'Metallic Bonding',
                'Molecular Structure',
            ],
        ];
    }

    private function getBiologyChapters(int $gradeLevel): array
    {
        return [
            'Chapter 1: Cell Biology' => [
                'Cell Structure',
                'Cell Organelles',
                'Cell Division',
                'Cell Differentiation',
            ],
            'Chapter 2: Life Processes' => [
                'Nutrition in Plants',
                'Nutrition in Animals',
                'Respiration',
                'Transportation',
                'Excretion',
            ],
            'Chapter 3: Human Body Systems' => [
                'Digestive System',
                'Respiratory System',
                'Circulatory System',
                'Nervous System',
                'Reproductive System',
            ],
            'Chapter 4: Genetics and Evolution' => [
                'Heredity',
                'DNA and Genes',
                'Mendel\'s Laws',
                'Evolution',
                'Natural Selection',
            ],
            'Chapter 5: Plant Biology' => [
                'Plant Structure',
                'Photosynthesis',
                'Plant Reproduction',
                'Plant Hormones',
            ],
            'Chapter 6: Ecology' => [
                'Ecosystems',
                'Food Chains and Webs',
                'Biodiversity',
                'Environmental Issues',
            ],
            'Chapter 7: Microorganisms' => [
                'Types of Microorganisms',
                'Beneficial Microorganisms',
                'Harmful Microorganisms',
                'Disease and Prevention',
            ],
        ];
    }

    private function getMyanmarChapters(int $gradeLevel): array
    {
        if ($gradeLevel <= 4) {
            return [
                'Chapter 1: Myanmar Alphabet' => [
                    'Consonants',
                    'Vowels',
                    'Tone Marks',
                    'Letter Formation',
                ],
                'Chapter 2: Reading Skills' => [
                    'Word Recognition',
                    'Simple Sentences',
                    'Story Reading',
                    'Comprehension',
                ],
                'Chapter 3: Writing Skills' => [
                    'Letter Writing',
                    'Word Writing',
                    'Sentence Formation',
                    'Creative Writing',
                ],
                'Chapter 4: Grammar Basics' => [
                    'Nouns',
                    'Verbs',
                    'Adjectives',
                    'Sentence Structure',
                ],
                'Chapter 5: Myanmar Culture' => [
                    'Traditional Stories',
                    'Festivals',
                    'Customs',
                ],
            ];
        } else {
            return [
                'Chapter 1: Myanmar Literature' => [
                    'Classical Poetry',
                    'Modern Literature',
                    'Short Stories',
                    'Drama',
                ],
                'Chapter 2: Advanced Grammar' => [
                    'Complex Sentences',
                    'Literary Devices',
                    'Formal Writing',
                    'Essay Writing',
                ],
                'Chapter 3: Myanmar History' => [
                    'Ancient Myanmar',
                    'Medieval Period',
                    'Colonial Era',
                    'Modern Myanmar',
                ],
                'Chapter 4: Cultural Studies' => [
                    'Traditional Arts',
                    'Music and Dance',
                    'Architecture',
                    'Social Customs',
                ],
                'Chapter 5: Composition' => [
                    'Descriptive Writing',
                    'Narrative Writing',
                    'Letter Writing',
                    'Report Writing',
                ],
            ];
        }
    }

    private function getHistoryChapters(int $gradeLevel): array
    {
        return [
            'Chapter 1: Ancient Civilizations' => [
                'Early Human Societies',
                'River Valley Civilizations',
                'Ancient Empires',
                'Cultural Developments',
            ],
            'Chapter 2: Medieval Period' => [
                'Feudal System',
                'Trade Routes',
                'Religious Movements',
                'Cultural Exchange',
            ],
            'Chapter 3: Age of Exploration' => [
                'European Explorers',
                'Colonial Expansion',
                'Trade and Commerce',
                'Cultural Impact',
            ],
            'Chapter 4: Modern History' => [
                'Industrial Revolution',
                'World Wars',
                'Independence Movements',
                'Contemporary World',
            ],
            'Chapter 5: Myanmar History' => [
                'Ancient Kingdoms',
                'Colonial Period',
                'Independence Struggle',
                'Modern Myanmar',
            ],
        ];
    }

    private function getGeographyChapters(int $gradeLevel): array
    {
        return [
            'Chapter 1: Physical Geography' => [
                'Earth\'s Structure',
                'Landforms',
                'Climate and Weather',
                'Natural Resources',
            ],
            'Chapter 2: Human Geography' => [
                'Population Distribution',
                'Settlements',
                'Economic Activities',
                'Transportation',
            ],
            'Chapter 3: Maps and Globes' => [
                'Map Reading',
                'Scale and Direction',
                'Latitude and Longitude',
                'Types of Maps',
            ],
            'Chapter 4: Continents and Oceans' => [
                'Major Continents',
                'Ocean Systems',
                'Climate Zones',
                'Natural Vegetation',
            ],
            'Chapter 5: Myanmar Geography' => [
                'Physical Features',
                'Climate',
                'Natural Resources',
                'Economic Geography',
            ],
        ];
    }

    private function getSocialStudiesChapters(int $gradeLevel): array
    {
        return [
            'Chapter 1: Our Community' => [
                'Family and Home',
                'Neighborhood',
                'Community Helpers',
                'Local Government',
            ],
            'Chapter 2: Culture and Society' => [
                'Festivals and Celebrations',
                'Traditional Customs',
                'Food and Clothing',
                'Arts and Crafts',
            ],
            'Chapter 3: Our Country' => [
                'National Symbols',
                'Geography of Myanmar',
                'States and Regions',
                'Major Cities',
            ],
            'Chapter 4: Rights and Responsibilities' => [
                'Children\'s Rights',
                'Civic Duties',
                'Rules and Laws',
                'Good Citizenship',
            ],
            'Chapter 5: Our World' => [
                'Countries and Continents',
                'Different Cultures',
                'Global Issues',
                'International Cooperation',
            ],
        ];
    }

    private function getArtChapters(int $gradeLevel): array
    {
        return [
            'Chapter 1: Drawing and Sketching' => [
                'Basic Shapes',
                'Lines and Patterns',
                'Shading Techniques',
                'Perspective Drawing',
            ],
            'Chapter 2: Painting' => [
                'Color Theory',
                'Watercolor Techniques',
                'Acrylic Painting',
                'Mixed Media',
            ],
            'Chapter 3: Craft and Design' => [
                'Paper Crafts',
                'Clay Modeling',
                'Textile Arts',
                'Recycled Art',
            ],
            'Chapter 4: Art History' => [
                'Traditional Art',
                'Modern Art',
                'Famous Artists',
                'Art Movements',
            ],
            'Chapter 5: Creative Expression' => [
                'Self-Expression',
                'Imagination',
                'Art Projects',
                'Exhibition',
            ],
        ];
    }

    private function getPhysicalEducationChapters(int $gradeLevel): array
    {
        return [
            'Chapter 1: Physical Fitness' => [
                'Warm-up Exercises',
                'Strength Training',
                'Flexibility',
                'Endurance',
            ],
            'Chapter 2: Sports and Games' => [
                'Team Sports',
                'Individual Sports',
                'Traditional Games',
                'Rules and Regulations',
            ],
            'Chapter 3: Health and Wellness' => [
                'Nutrition',
                'Personal Hygiene',
                'First Aid',
                'Injury Prevention',
            ],
            'Chapter 4: Athletics' => [
                'Running',
                'Jumping',
                'Throwing',
                'Track Events',
            ],
            'Chapter 5: Yoga and Meditation' => [
                'Basic Yoga Poses',
                'Breathing Exercises',
                'Relaxation Techniques',
                'Mental Wellness',
            ],
        ];
    }

    private function getGenericChapters(int $gradeLevel): array
    {
        return [
            'Chapter 1: Introduction' => [
                'Basic Concepts',
                'Fundamental Principles',
                'Key Terms',
            ],
            'Chapter 2: Core Topics' => [
                'Topic 1',
                'Topic 2',
                'Topic 3',
            ],
            'Chapter 3: Advanced Concepts' => [
                'Advanced Topic 1',
                'Advanced Topic 2',
                'Advanced Topic 3',
            ],
            'Chapter 4: Applications' => [
                'Practical Applications',
                'Real-world Examples',
                'Case Studies',
            ],
            'Chapter 5: Review and Assessment' => [
                'Summary',
                'Practice Problems',
                'Assessment',
            ],
        ];
    }
}
